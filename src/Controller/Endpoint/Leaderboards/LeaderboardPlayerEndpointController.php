<?php

namespace Ps2alerts\Api\Controller\Endpoint\Leaderboards;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Controller\Endpoint\Leaderboards\AbstractLeaderboardEndpointController;
use Ps2alerts\Api\Exception\CensusEmptyException;
use Ps2alerts\Api\Exception\CensusErrorException;
use Ps2alerts\Api\Repository\Metrics\PlayerTotalRepository;
use Ps2alerts\Api\Transformer\Leaderboards\PlayerLeaderboardTransformer;
use Ps2alerts\Api\Controller\Endpoint\Data\DataEndpointController;

class LeaderboardPlayerEndpointController extends AbstractLeaderboardEndpointController
{
    protected $dataEndpoint;
    protected $repository;

    /**
     * Construct
     *
     * @param League\Fractal\Manager $fractal
     */
    public function __construct(
        DataEndpointController $dataEndpoint,
        Manager                $fractal,
        PlayerTotalRepository  $repository
    ) {
        $this->fractal = $fractal;
        $this->repository = $repository;
        $this->dataEndpoint = $dataEndpoint;
    }

    /**
     * Get Player Leaderboard
     *
     * @return \League\Fractal\Manager
     */
    public function players()
    {
        $valid = $this->validateRequestVars();

        // If validation didn't pass, chuck 'em out
        if ($valid !== true) {
            return $this->errorWrongArgs($valid->getMessage());
        }

        $server = $_GET['server'];
        $limit  = $_GET['limit'];
        $offset = $_GET['offset'];

        // Translate field into table specific columns
        if (isset($_GET['field'])) {
            $field = $this->getField($_GET['field']);
        }

        if (! isset($field)) {
            return $this->errorWrongArgs('Field wasn\'t provided and is required.');
        }

        // Perform Query
        $query = $this->repository->newQuery();
        $query->cols(['*']);
        $query->orderBy(["{$field} desc"]);

        if (isset($server)) {
            $query->where('playerServer = ?', $server);
        }

        if (isset($limit)) {
            $query->limit($limit);
        } else {
            $query->limit(10); // Set default limit
        }

        if (isset($offset)) {
            $query->offset($offset);
        }

        $players = $this->repository->fireStatementAndReturn($query);

        $count = count($players);

        // Gets outfit details
        for ($i = 0; $i < $count; $i++) {
            if (! empty($players[$i]['playerOutfit'])) {
                // Gets outfit details
                try {
                    $outfit = $this->dataEndpoint->getOutfit($players[$i]['playerOutfit']);
                } catch (CensusErrorException $e) {
                    $outfit = null;
                } catch (CensusEmptyException $e) {
                    $outfit = null;
                }

                $players[$i]['playerOutfit'] = $outfit;
            }
        }

        return $this->respond(
            'collection',
            $players,
            new PlayerLeaderboardTransformer
        );
    }

    /**
     * Gets the appropiate field for the table and handles some table naming oddities
     * @param  string $input Field to look at
     * @return string
     */
    public function getField($input) {
        $field = null;

        switch ($input) {
            case 'kills':
                $field = 'playerKills';
                break;
            case 'deaths':
                $field = 'playerDeaths';
                break;
            case 'teamkills':
                $field = 'playerTeamKills';
                break;
            case 'suicides':
                $field = 'playerSuicides';
                break;
            case 'headshots':
                $field = 'headshots';
                break;
        }

        return $field;
    }
}
