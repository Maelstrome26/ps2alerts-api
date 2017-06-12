<?php

namespace Ps2alerts\Api\Controller\Endpoint\Leaderboards;

use League\Fractal\Manager;
use Ps2alerts\Api\Contract\RedisAwareInterface;
use Ps2alerts\Api\Contract\RedisAwareTrait;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Exception\InvalidArgumentException;
use Ps2alerts\Api\Repository\Metrics\OutfitTotalRepository;
use Ps2alerts\Api\Repository\Metrics\PlayerTotalRepository;
use Ps2alerts\Api\Repository\Metrics\WeaponTotalRepository;
use Ps2alerts\Api\Transformer\Leaderboards\OutfitLeaderboardTransformer;
use Ps2alerts\Api\Transformer\Leaderboards\PlayerLeaderboardTransformer;
use Ps2alerts\Api\Transformer\Leaderboards\WeaponLeaderboardTransformer;
use Ps2alerts\Api\Transformer\Leaderboards\LeaderboardUpdatedTransformer;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class LeaderboardEndpointController extends AbstractEndpointController implements
    RedisAwareInterface
{
    use RedisAwareTrait;

    /**
     * Construct
     *
     * @param League\Fractal\Manager $fractal
     */
    public function __construct(
        Manager               $fractal,
        PlayerTotalRepository $playerTotalRepository,
        OutfitTotalRepository $outfitTotalRepository,
        WeaponTotalRepository $weaponTotalRepository
    ) {

        $this->fractal = $fractal;
        $this->playerTotalRepository = $playerTotalRepository;
        $this->outfitTotalRepository = $outfitTotalRepository;
        $this->weaponTotalRepository = $weaponTotalRepository;
    }

    /**
     * Get Player Leaderboard
     *
     * @param  Psr\Http\Message\ServerRequestInterface  $request
     * @param  Psr\Http\Message\ResponseInterface $response
     *
     * @return League\Fractal\Manager
     */
    public function players(ServerRequestInterface $request, ResponseInterface $response)
    {
        $valid = $this->validateRequestVars($request);

        // If validation didn't pass, chuck 'em out
        if ($valid !== true) {
            return $this->errorWrongArgs($response, $valid->getMessage());
        }

        $field  = $_GET['field'];
        $server = $_GET['server'];
        $limit  = $_GET['limit'];
        $offset = $_GET['offset'];

        // Translate field into table specific columns

        // Default
        if (! isset($field)) {
            $field = 'playerKills';
        }

        if (isset($field)) {
            switch ($field) {
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
        }

        // Perform Query
        $query = $this->playerTotalRepository->newQuery();
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

        $players = $this->playerTotalRepository->fireStatementAndReturn($query);

        $count = count($players);

        // Get's outfit details
        for ($i = 0; $i < $count; $i++) {
            if (! empty($players[$i]['playerOutfit'])) {
                // Gets outfit details
                $data = $this->getPlayerOutfit($players[$i]['playerOutfit']);

                if (! isset($data) || empty($data)) {
                    $outfit = null;
                } else {
                    $outfit = [
                        'id'     => $data['outfitID'],
                        'name'   => $data['outfitName'],
                        'tag'    => $data['outfitTag'],
                        'server' => (int) $data['outfitServer']
                    ];
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
     * Get Outfit Leaderboard
     *
     * @param  Psr\Http\Message\ServerRequestInterface  $request
     * @param  Psr\Http\Message\ResponseInterface $response
     *
     * @return League\Fractal\Manager
     */
    public function outfits(ServerRequestInterface $request, ResponseInterface $response)
    {
        $valid = $this->validateRequestVars($request);

        // If validation didn't pass, chuck 'em out
        if ($valid !== true) {
            return $this->errorWrongArgs($response, $valid->getMessage());
        }

        $field  = $_GET['field'];
        $server = $_GET['server'];
        $limit  = $_GET['limit'];
        $offset = $_GET['offset'];

        // Translate field into table specific columns

        // Default
        if (! isset($field)) {
            $field = 'outfitKills';
        }

        if (isset($field)) {
            switch ($field) {
                case 'kills':
                    $field = 'outfitKills';
                    break;
                case 'deaths':
                    $field = 'outfitDeaths';
                    break;
                case 'teamkills':
                    $field = 'outfitTKs';
                    break;
                case 'suicides':
                    $field = 'outfitSuicides';
                    break;
                case 'headshots':
                    $field = 'headshots';
                    break;
                case 'captures':
                    $field = 'outfitCaptures';
                    break;
            }
        }

        // Perform Query
        $query = $this->outfitTotalRepository->newQuery();
        $query->cols(['*']);
        $query->orderBy(["{$field} desc"]);
        $query->where('outfitID > 0');

        if (isset($server)) {
            $query->where('outfitServer = ?', $server);
        }

        if (isset($limit)) {
            $query->limit($limit);
        } else {
            $query->limit(10); // Set default limit
        }

        if (isset($offset)) {
            $query->offset($offset);
        }

        return $this->respond(
            'collection',
            $this->outfitTotalRepository->fireStatementAndReturn($query),
            new OutfitLeaderboardTransformer
        );
    }

    /**
     * Get Weapon Leaderboard
     *
     * @param  Psr\Http\Message\ServerRequestInterface  $request
     * @param  Psr\Http\Message\ResponseInterface $response
     *
     * @return League\Fractal\Manager
     */
    public function weapons(ServerRequestInterface $request, ResponseInterface $response)
    {
        $valid = $this->validateRequestVars($request);

        // If validation didn't pass, chuck 'em out
        if ($valid !== true) {
            return $this->errorWrongArgs($response, $valid->getMessage());
        }

        $field  = $_GET['field'];

        // Translate field into table specific columns

        // Default
        if (! isset($field)) {
            $field = 'killCount';
        }

        if (isset($field)) {
            switch ($field) {
                case 'kills':
                    $field = 'killCount';
                    break;
                case 'headshots':
                    $field = 'headshots';
                    break;
                case 'teamkills':
                    $field = 'teamkills';
                    break;
            }
        }

        $redis = $this->getRedisDriver();
        $key = "ps2alerts:api:leaderboards:weapons:{$field}";

        // If we have this cached already
        if (! empty($redis->exists($key))) {
            $weapons = json_decode($redis->get($key), true);
        } else {
            // Perform Query
            $query = $this->weaponTotalRepository->newQuery();
            $query->cols([
                'weaponID',
                'SUM(killCount) as killCount',
                'SUM(teamkills) as teamkills',
                'SUM(headshots) as headshots'
            ]);
            $query->where('weaponID > 0');
            $query->orderBy(["{$field} desc"]);
            $query->groupBy(['weaponID']);

            $weapons = $this->weaponTotalRepository->fireStatementAndReturn($query);

            // Cache results in redis
            $redis->setEx($key, 7200, json_encode($weapons));
        }

        return $this->respond(
            'collection',
            $weapons,
            new WeaponLeaderboardTransformer
        );
    }

    /**
     * Gets a players outfit either from DB or Redis
     *
     * @param  string $outfitID
     *
     * @return array
     */
    public function getPlayerOutfit($outfitID)
    {
        $redis = $this->getRedisDriver();
        $key = "ps2alerts:api:outfits:{$outfitID}";

        // If we have this cached already
        if (! empty($redis->exists($key))) {
            return json_decode($redis->get($key), true);
        }

        $outfit = $this->outfitTotalRepository->readSingleById($outfitID, 'outfitID');

        // Cache results in redis
        $redis->setEx($key, 86400, json_encode($outfit));

        return $outfit;
    }

    /**
     * Validates the request variables
     *
     * @param  Psr\Http\Message\ServerRequestInterface  $request
     *
     * @return boolean|InvalidArgumentException
     */
    public function validateRequestVars($request)
    {
        try {
            if (! empty($_GET['field'])) {
                $this->parseField($_GET['field']);
            }

            if (! empty($_GET['server'])) {
                $this->parseServer($_GET['server']);
            }

            if (! empty($_GET['limit'])) {
                $this->parseOffset($_GET['limit']);
            }

            if (! empty($_GET['offset'])) {
                $this->parseOffset($_GET['offset']);
            }
        } catch (InvalidArgumentException $e) {
            return $e;
        }

        return true;
    }

    /**
     * Validate the field requested
     *
     * @return string
     */
    public function parseField($field)
    {
        $validFields = [
            'kills',
            'deaths',
            'teamkills',
            'suicides',
            'headshots',
            'captures'
        ];

        if (! empty($field) && in_array($field, $validFields)) {
            return $field;
        }

        throw new InvalidArgumentException("Field '{$field}' is not supported.");
    }

    /**
     * Validate the server requested
     *
     * @return string
     */
    public function parseServer($server)
    {
        $validServers = $this->getConfigItem('servers');

        // Remove Jaeger
        if (($key = array_search(19, $validServers)) !== false) {
            unset($validServers[$key]);
        }

        if (! empty($server) && in_array($server, $validServers)) {
            return $server;
        }

        throw new InvalidArgumentException("Server '{$server}' is not supported.");
    }

    /**
     * Parses limit, making sure it's numerical and valid
     *
     * @return boolean
     */
    public function parseLimit($limit)
    {
        if (! isset($limit) && ! is_numeric($limit)) {
            throw new InvalidArgumentException("Limit needs to be in numerical format.");
        }

        return $limit;
    }

    /**
     * Parses offset, making sure it's numerical and valid
     */
    public function parseOffset($offset)
    {
        if (! isset($offset) && ! is_numeric($offset)) {
            throw new InvalidArgumentException("Offset needs to be in numerical format.");
        }

        return $offset;
    }
}
