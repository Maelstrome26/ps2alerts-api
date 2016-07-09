<?php

namespace Ps2alerts\Api\Controller\Endpoint\Data;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Transformer\DataTransformer;
use Ps2alerts\Api\Transformer\Data\CharacterTransformer;
use Ps2alerts\Api\Transformer\Data\OutfitTransformer;
use Ps2alerts\Api\Contract\HttpClientAwareInterface;
use Ps2alerts\Api\Contract\HttpClientAwareTrait;
use Ps2alerts\Api\Contract\RedisAwareInterface;
use Ps2alerts\Api\Contract\RedisAwareTrait;
use Ps2alerts\Api\Exception\CensusErrorException;
use Ps2alerts\Api\Exception\CensusEmptyException;
use Ps2alerts\Api\Exception\RedisStoreException;
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
    public function character(Request $request, Response $response, array $args)
    {
        // First, check if we have the character in Redis
        $character = $this->checkRedis('character', $args['id']);

        // If not, pull it from Census and store it
        if (empty($character)) {
            try {
                $character = $this->getCharacter($args['id']);
            } catch (CensusErrorException $e) {
                $this->setStatusCode(500);
                return $this->respondWithError($response, 'Census returned garbage!', 'CENSUS_ERROR');
            } catch (CensusEmptyException $e) {
                $this->setStatusCode(404);
                return $this->respondWithError($response, 'Census returned no data!', 'CENSUS_ERROR');
            } catch (RedisStoreException $e) {
                $this->setStatusCode(500);
                return $this->respondWithError($response, 'Redis store failed!', 'INTERNAL_ERROR');
            }
        }

        // Now return the character the outfit injected
        if (! empty($character['data']['outfit'])) {
            try {
                $outfit = $this->getOutfit($character['data']['outfit']);
            }catch (CensusErrorException $e) {
                $this->setStatusCode(500);
                return $this->respondWithError($response, 'Census returned garbage!', 'CENSUS_ERROR');
            } catch (CensusEmptyException $e) {
                $this->setStatusCode(404);
                return $this->respondWithError($response, 'Census returned no data!', 'CENSUS_ERROR');
            } catch (RedisStoreException $e) {
                $this->setStatusCode(500);
                return $this->respondWithError($response, 'Redis store failed!', 'INTERNAL_ERROR');
            }

            $character['data']['outfit'] = $outfit['data'];
        }

        return $this->respondWithArray($response, $character);
    }

    /**
     * Gets an outfits's info, either from redis or db cache
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     * @param  array                                     $args
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function outfit(Request $request, Response $response, array $args)
    {
        try {
            $outfit = $this->getOutfit($args['id']);
        } catch (CensusErrorException $e) {
            $this->setStatusCode(500);
            return $this->respondWithError($response, 'Census returned garbage!', 'CENSUS_ERROR');
        } catch (CensusEmptyException $e) {
            $this->setStatusCode(404);
            return $this->respondWithError($response, 'Census returned no data!', 'CENSUS_ERROR');
        } catch (RedisStoreException $e) {
            $this->setStatusCode(500);
            return $this->respondWithError($response, 'Redis store failed!', 'INTERNAL_ERROR');
        }

        return $this->respondWithArray($response, $outfit);
    }

    /**
     * Gets the character from Census with a supplied ID
     *
     * @param  string $id
     *
     * @return array
     */
    public function getCharacter($id)
    {
        // Since we don't have any data, let's grab it from Census.
        $endpoint = "character?character_id={$id}&c:resolve=outfit";

        try {
            $json = $this->sendCensusQuery($endpoint);
        } catch (\Exception $e) {
            throw new CensusErrorException();
        }

        // If the character is empty...
        // SCENARIOS: Character has been banned or deleted
        if ($json === null || empty($json->character_list)) {
            throw new CensusEmptyException();
        }

        $env = $json->environment;
        $json = $json->character_list[0];
        $json->environment = $env; // Inject the ENV to store

        $character = $this->createItem($json, new CharacterTransformer);

        // First store the player without an outfit so we're not storing duplicated data
        try {
            $this->storeInRedis('character', $id, $character['data']);
        } catch (\Exception $e) {
            $this->setStatusCode(500);
            return $this->respondWithError($response, 'Redis store failed!', 'INTERNAL_ERROR');
        }

        return $character;
    }


    /**
     * Gets an outfit from either Redis or Census
     *
     * @param  string $id
     *
     * @return array
     */
    public function getOutfit($id)
    {
        // First, check if we have the outfit in Redis
        $redisCheck = $this->checkRedis('outfit', $id);

        if (! empty($redisCheck)) {
            return $redisCheck;
        }

        // Since we don't have any data, let's grab it from Census.
        $endpoint = "outfit?outfit_id={$id}&c:resolve=leader";

        try {
            $json = $this->sendCensusQuery($endpoint);
        } catch (\Exception $e) {
            throw new CensusErrorException();
        }

        // If the outfit is empty...
        // SCENARIOS: Outfit has been deleted
        if ($json === null || empty($json->outfit_list)) {
            throw new CensusEmptyException();
        }

        $env = $json->environment;
        $outfit = $json->outfit_list[0];
        $outfit->environment = $env; // Inject the ENV to store

        $outfit = $this->createItem($outfit, new OutfitTransformer);

        try {
            $this->storeInRedis('outfit', $id, $outfit['data']);
        } catch (\Exception $e) {
            throw new RedisStoreException();
        }

        return $outfit;
    }

    /**
     * Allows the sending of queries to census, along with checking all environments
     *
     * @param  string $endpoint Endpoint string to get data from
     *
     * @return string|json
     */
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
            $url = "https://census.daybreakgames.com/s:{$config['census_service_id']}/get/{$env}/{$endpoint}";

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
            $data = json_decode($redis->get($key), true);

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
