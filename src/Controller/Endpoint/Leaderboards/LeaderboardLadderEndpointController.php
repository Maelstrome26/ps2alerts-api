<?php

namespace Ps2alerts\Api\Controller\Endpoint\Leaderboards;

use League\Fractal\Manager;
use Ps2alerts\Api\Contract\RedisAwareInterface;
use Ps2alerts\Api\Contract\RedisAwareTrait;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Repository\Metrics\OutfitTotalRepository;
use Ps2alerts\Api\Repository\Metrics\PlayerTotalRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LeaderboardLadderEndpointController extends AbstractEndpointController implements
    RedisAwareInterface
{
    use RedisAwareTrait;

    

    /**
     * Prompts the Leaderboard:Check command to resync the leaderboards
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function update(Request $request, Response $response)
    {
        $config = $this->getConfig();

        // Only accept commands from internal IP
        $ip = $request->getClientIp();

        if ($ip !== $_SERVER['SERVER_ADDR']) {
            $response->setStatusCode(404);
            return $response;
        }

        $server = $request->get('server');

        $redis = $this->getRedisDriver();
        $key = "ps2alerts:api:leaderboards:status:{$server}";

        // If we have a key, change the flag to force exists so the cronjob can run
        if ($redis->exists($key)) {
            $data = json_decode($redis->get($key));

             // Ignore if already flagged as being updated
            if ($data->beingUpdated == 0) {
                $data->forceUpdate = 1;
                $redis->set($key, json_encode($data));
            }
        } else {
            // Panic.
        }

        $response->setStatusCode(202);
        return $response;
    }

    /**
     * Returns a list of times that a server leaderboard has been updated
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function lastUpdate(Request $request, Response $response)
    {
        $config = $this->getConfig();
        $redis = $this->getRedisDriver();

        $data = [];

        foreach($config['servers'] as $server) {
            $key = "ps2alerts:api:leaderboards:status:{$server}";

            if ($redis->exists($key)) {
                $entry = json_decode($redis->get($key));
                $data[$server] = $this->createItem($entry, new LeaderboardUpdatedTransformer);
            }
        }

        return $this->respondWithArray($response, $data);
    }
}
