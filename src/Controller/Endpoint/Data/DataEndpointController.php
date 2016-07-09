<?php

namespace Ps2alerts\Api\Controller\Endpoint\Data;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Transformer\DataTransformer;
use Ps2alerts\Api\Transformer\Data\CharacterTransformer;
use Ps2alerts\Api\Contract\HttpClientAwareInterface;
use Ps2alerts\Api\Contract\HttpClientAwareTrait;
use Ps2alerts\Api\Contract\RedisAwareInterface;
use Ps2alerts\Api\Contract\RedisAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DataEndpointController extends AbstractEndpointController implements
    HttpClientAwareInterface,
    RedisAwareInterface
{
    use HttpClientAwareTrait;
    use RedisAwareTrait;

    /**
     * Construct
     *
     * @param League\Fractal\Manager                    $fractal
     */
    public function __construct(Manager $fractal) {
        $this->fractal = $fractal;
    }

    /**
     * Gets supplemental data
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     * @param  array                                     $args
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getSupplementalData(Request $request, Response $response, array $args)
    {
        // All data handling is done within the transformer.
        return $this->respond(
            'item',
            null,
            new DataTransformer,
            $request,
            $response
        );
    }

    /**
     * Gets a player's info, either from redis or db cache
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     * @param  array                                     $args
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function player(Request $request, Response $response, array $args)
    {
        // First, check if we have the player in Redis
        $redisCheck = $this->checkRedis('player', $args['id']);

        if (! empty($redisCheck)) {
            return $this->respondWithArray($response, $redisCheck);
        }

        // Since we don't have any data, let's grab it from Census.
        $endpoint = "character?character_id={$args['id']}&c:resolve=outfit";

        try {
            $character = $this->sendCensusQuery($endpoint);
        } catch (\Exception $e) {
            $this->setStatusCode(500);
            return $this->respondWithError($response, 'Census returned garbage!', 'CENSUS_ERROR');
        }

        // If the character is empty...
        // SCENARIOS: Character has been banned or deleted
        if ($character === null || empty($character->character_list)) {
            $this->setStatusCode(404);
            return $this->respondWithError($response, 'Census returned no data!', 'CENSUS_ERROR');
        }

        $env = $character->environment;
        $character = $character->character_list[0];
        $character->environment = $env; // Inject the ENV to store

        $character = $this->createItem($character, new CharacterTransformer);

        try {
            $this->storeInRedis('player', $args['id'], $character['data']);
        } catch (\Exception $e) {
            $this->setStatusCode(500);
            return $this->respondWithError($response, 'Redis store failed!', 'INTERNAL_ERROR');
        }

        return $this->respondWithArray($response, $character);
    }

    public function sendCensusQuery($endpoint)
    {
        $config = $this->getConfig();
        $guzzle = $this->getHttpClientDriver();

        $environments = [
            'ps2:v2',
            'ps2ps4us',
            'ps2ps4eu'
        ];

        // Loop through each environment and get the first result
        foreach($environments as $env) {
            $url  = "https://census.daybreakgames.com/s:{$config['census_service_id']}/get/{$env}/{$endpoint}";
            $req  = $guzzle->request('GET', $url);
            $body = $req->getBody();
            $json = json_decode($body);

            // Check for errors #BRINGJSONEXCEPTIONS!
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception();
            }

            // Append the environment so we can store it for later
            $json->environment = $env;

            if ($json->returned !== 0) {
                return $json;
            }
        }
    }

    /**
     * Checks redis for a entry and returns it decoded if exists
     *
     * @param  string $type player|outfit
     * @param  string $id   ID of player or outfit
     *
     * @return string|boolean
     */
    public function checkRedis($type, $id)
    {
        $redis = $this->getRedisCacheDriver();

        $key = "ps2alerts:cache:{$type}:{$id}";

        if ($redis->exists($key)) {
            $data = json_decode($redis->get($key));

            $return['data'] = $data; // Format it like the rest of the endpoints
            return $return;
        }

        return false;
    }

    /**
     * Stores the complete information in Redis
     *
     * @param  string  $type
     * @param  string  $id
     * @param  string  $data
     *
     * @return boolean
     */
    public function storeInRedis($type, $id, $data)
    {
        $redis = $this->getRedisCacheDriver();

        $key = "ps2alerts:cache:{$type}:{$id}";

        $data = json_encode($data);

        // Check for errors #BRINGJSONEXCEPTIONS!
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception();
        }

        $cacheTime = 3600 * 24;

        return $redis->setEx($key, $cacheTime, $data);
    }
}
