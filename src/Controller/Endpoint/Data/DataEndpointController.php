<?php

namespace Ps2alerts\Api\Controller\Endpoint\Data;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Transformer\DataTransformer;
use Ps2alerts\Api\Transformer\Data\CharacterTransformer;
use Ps2alerts\Api\Transformer\Data\OutfitTransformer;
use Ps2alerts\Api\Contract\HttpClientAwareInterface;
use Ps2alerts\Api\Contract\HttpClientAwareTrait;
use Ps2alerts\Api\Exception\CensusErrorException;
use Ps2alerts\Api\Exception\CensusEmptyException;
use Ps2alerts\Api\Exception\RedisStoreException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class DataEndpointController extends AbstractEndpointController implements
    HttpClientAwareInterface
{
    use HttpClientAwareTrait;

    protected $dataTransformer;

    /**
     * Construct
     *
     * @param League\Fractal\Manager                    $fractal
     */
    public function __construct(
        Manager $fractal,
        DataTransformer $dataTransformer
    ) {
        $this->fractal = $fractal;
        $this->dataTransformer = $dataTransformer;
    }

    /**
     * Gets supplemental data
     *
     * @param  Psr\Http\Message\ServerRequestInterface  $request
     * @param  Psr\Http\Message\ResponseInterface $response
     * @param  array                                     $args
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getSupplementalData(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        // All data handling is done within the transformer.
        return $this->respond(
            'item',
            null,
            $this->dataTransformer
        );
    }

    /**
     * Gets a player's info, either from redis or db cache
     *
     * @param  Psr\Http\Message\ServerRequestInterface  $request
     * @param  Psr\Http\Message\ResponseInterface $response
     * @param  array                                     $args
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function character(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        // First, check if we have the character in Redis
        $character['data'] = $this->checkRedis('cache', 'character', $args['id']);

        // If not, pull it from Census and store it
        if (empty($character)) {
            try {
                $character = $this->getCharacter($args['id']);
            } catch (CensusErrorException $e) {
                $this->setStatusCode(500);
                return $this->respondWithError('Census returned garbage!', 'CENSUS_ERROR');
            } catch (CensusEmptyException $e) {
                $this->setStatusCode(404);
                return $this->respondWithError('Census returned no data!', 'CENSUS_ERROR');
            } catch (RedisStoreException $e) {
                $this->setStatusCode(500);
                return $this->respondWithError('Redis store failed!', 'INTERNAL_ERROR');
            }
        }

        // Now return the character the outfit injected
        if (! empty($character['data']['outfit'])) {
            try {
                $outfit = $this->getOutfit($character['data']['outfit']);
            } catch (CensusErrorException $e) {
                $this->setStatusCode(500);
                return $this->respondWithError('Census returned garbage!', 'CENSUS_ERROR');
            } catch (CensusEmptyException $e) {
                $this->setStatusCode(404);
                return $this->respondWithError('Census returned no data!', 'CENSUS_ERROR');
            } catch (RedisStoreException $e) {
                $this->setStatusCode(500);
                return $this->respondWithError('Redis store failed!', 'INTERNAL_ERROR');
            }

            $character['data']['outfit'] = $outfit['data'];
        }

        return $this->respondWithArray($character);
    }

    /**
     * Gets an outfits's info, either from redis or db cache
     *
     * @param  Psr\Http\Message\ServerRequestInterface  $request
     * @param  Psr\Http\Message\ResponseInterface $response
     * @param  array                                     $args
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function outfit(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        try {
            $outfit = $this->getOutfit($args['id']);
        } catch (CensusErrorException $e) {
            $this->setStatusCode(500);
            return $this->respondWithError('Census returned garbage!', 'CENSUS_ERROR');
        } catch (CensusEmptyException $e) {
            $this->setStatusCode(404);
            return $this->respondWithError('Census returned no data!', 'CENSUS_ERROR');
        } catch (RedisStoreException $e) {
            $this->setStatusCode(500);
            return $this->respondWithError('Redis store failed!', 'INTERNAL_ERROR');
        }

        return $this->respondWithArray($outfit);
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
        $json->environment = $env; // Re-inject the ENV to store

        $character = $this->createItem($json, new CharacterTransformer);

        // First store the player without an outfit so we're not storing duplicated data
        try {
            $this->storeInRedis('cache', 'character', $id, $character['data']);
        } catch (\Exception $e) {
            $this->setStatusCode(500);
            return $this->respondWithError('Redis store failed!', 'INTERNAL_ERROR');
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
        $redisCheck['data'] = $this->checkRedis('cache', 'outfit', $id);

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
        $outfit->environment = $env; // Re-inject the ENV to store

        $outfit = $this->createItem($outfit, new OutfitTransformer);

        try {
            $this->storeInRedis('cache', 'outfit', $id, $outfit['data']);
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


}
