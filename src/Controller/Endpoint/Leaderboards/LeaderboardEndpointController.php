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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        OutfitTotalRepository $outfitTotalRepository
    ) {

        $this->fractal = $fractal;
        $this->playerTotalRepository = $playerTotalRepository;
        $this->outfitTotalRepository = $outfitTotalRepository;
    }

    /**
     * Get Player Leaderboard
     *
     * @param  Request  $request  [description]
     * @param  Response $response [description]
     *
     * @return [type]             [description]
     */
    public function players(Request $request, Response $response)
    {
        $valid = $this->validateRequestVars($request);

        // If validation didn't pass, chuck 'em out
        if ($valid !== true) {
            return $this->errorWrongArgs($response, $valid->getMessage());
        }

        $field  = $request->get('field');
        $server = $request->get('server');
        $limit  = $request->get('limit');
        $offset = $request->get('offset');

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
            new PlayerLeaderboardTransformer,
            $request,
            $response
        );
    }

    /**
     * Get Outfit Leaderboard
     *
     * @param  Request  $request  [description]
     * @param  Response $response [description]
     *
     * @return [type]             [description]
     */
    public function outfits(Request $request, Response $response)
    {
        $valid = $this->validateRequestVars($request);

        // If validation didn't pass, chuck 'em out
        if ($valid !== true) {
            return $this->errorWrongArgs($response, $valid->getMessage());
        }

        $field  = $request->get('field');
        $server = $request->get('server');
        $limit  = $request->get('limit');
        $offset = $request->get('offset');

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
            }
        }

        // Perform Query
        $query = $this->outfitTotalRepository->newQuery();
        $query->cols(['*']);
        $query->orderBy(["{$field} desc"]);

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
            new OutfitLeaderboardTransformer,
            $request,
            $response
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
     * @param  Symfony\Component\HttpFoundation\Request  $request
     *
     * @return boolean|InvalidArgumentException
     */
    public function validateRequestVars($request)
    {
        try {
            if (! empty($request->get('field'))) {
                $this->parseField($request->get('field'));
            }

            if (! empty($request->get('server'))) {
                $this->parseServer($request->get('server'));
            }

            if (! empty($request->get('limit'))) {
                $this->parseOffset($request->get('limit'));
            }

            if (! empty($request->get('offset'))) {
                $this->parseOffset($request->get('offset'));
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
            'headshots'
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
