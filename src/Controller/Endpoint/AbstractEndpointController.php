<?php

namespace Ps2alerts\Api\Controller\Endpoint;

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Ps2alerts\Api\Contract\ConfigAwareInterface;
use Ps2alerts\Api\Contract\ConfigAwareTrait;
use Ps2alerts\Api\Contract\DatabaseAwareInterface;
use Ps2alerts\Api\Contract\DatabaseAwareTrait;
use Ps2alerts\Api\Contract\HttpMessageAwareInterface;
use Ps2alerts\Api\Contract\HttpMessageAwareTrait;
use Ps2alerts\Api\Contract\RedisAwareInterface;
use Ps2alerts\Api\Contract\RedisAwareTrait;
use Ps2alerts\Api\Exception\InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractEndpointController implements
    ConfigAwareInterface,
    DatabaseAwareInterface,
    HttpMessageAwareInterface,
    RedisAwareInterface
{
    use ConfigAwareTrait;
    use DatabaseAwareTrait;
    use HttpMessageAwareTrait;
    use RedisAwareTrait;

    /**
     * Contains the repository used for interfacing with the database
     *
     * @var \Ps2alerts\Api\Repository\AbstractEndpointRepository
     */
    protected $repository;

    /**
     * Stores the status code
     *
     * @var integer
     */
    protected $statusCode = 200;

    /**
     * Flag whether to send back a "is cached" header
     *
     * @var boolean
     */
    protected $sendCachedHeader = false;

    /**
     * Holds Fractal
     *
     * @var \League\Fractal\Manager
     */
    protected $fractal;

    /**
     * Holds the transformer we're going to use
     *
     * @var mixed|\Leage\Fractal\TransformerAbstract
     */
    protected $transformer;

    const CODE_WRONG_ARGS     = 'API-MALFORMED-REQUEST';
    const CODE_NOT_FOUND      = 'API-NOT-FOUND';
    const CODE_INTERNAL_ERROR = 'API-DOH';
    const CODE_UNAUTHORIZED   = 'API-UNAUTHORIZED';
    const CODE_FORBIDDEN      = 'API-DENIED';
    const CODE_EMPTY          = 'API-EMPTY';

    /**
     * Getter for statusCode
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for statusCode
     *
     * @param int $statusCode Value to set
     *
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Master function to split out appropiate calls
     *
     * @param  string                                     $kind     The kind of data we wish to return
     * @param  array                                      $data     The data itself
     * @param  \League\Fractal\TransformerAbstract        $callback The transformer class to call
     * @param  \Psr\Http\Message\ServerRequestInterface  $request  The request itself
     * @param  \Psr\Http\Message\ResponseInterface $response The response object to eventually call
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function respond($kind, $data, $callback)
    {
        // Detect what embeds we need
        $this->getIncludesFromRequest();
        switch ($kind) {
            case 'item':
                return $this->respondWithItem($data, $callback);
            case 'collection':
                return $this->respondWithCollection($data, $callback);
            default:
                return $this->errorInternalError('No Response was defined. Please report this.');
        }
    }

    /**
     * Builds an item response in Fractal then hands off to the responder
     *
     * @param  array                                      $item        The item to transform
     * @param  \League\Fractal\TransformerAbstract        $transformer The Transformer to pass through to Fractal
     * @param  \Psr\Http\Message\ResponseInterface $response    The client's response
     *
     * @return array
     */
    protected function respondWithItem($item, $transformer)
    {
        return $this->respondWithArray($this->createItem($item, $transformer));
    }

    /**
     * Creates the item array and returns it hence it came.
     *
     * @param  array                               $item        The data to parse
     * @param  \League\Fractal\TransformerAbstract $transformer
     *
     * @return array
     */
    public function createItem($item, $transformer)
    {
        $resource = new Item($item, $transformer);
        $rootScope = $this->fractal->createData($resource);

        return $rootScope->toArray();
    }

    /**
     * Builds a collection of items from Fractal then hands off to the responder
     *
     * @param  array                                      $collection  The collection to transform
     * @param  \League\Fractal\TransformerAbstract        $transformer The Transformer to pass through to Fractal
     * @param  \Psr\Http\Message\ResponseInterface $response    The client's response
     *
     * @return array
     */
    protected function respondWithCollection($collection, $transformer)
    {
        return $this->respondWithArray($this->createCollection($collection, $transformer));
    }

    /**
     * Creates a collection array and sends it back to hence it came.
     *
     * @param  array                               $collection
     * @param  \League\Fractal\TransformerAbstract $transformer
     *
     * @return array
     */
    public function createCollection($collection, $transformer)
    {
        $resource = new Collection($collection, $transformer);
        $rootScope = $this->fractal->createData($resource);

        return $rootScope->toArray();
    }

    /**
     * The final step where the formatted array is now sent back as a response in JSON form
     *
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @param  array                                      $array
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function respondWithArray(array $array)
    {
        $response = $this->getResponse();
        $request = $this->getRequest();

        $response->getBody()->write(json_encode($array));

        $response = $response->withStatus($this->getStatusCode());
        $response = $response->withHeader('Content-Type', 'application/json');

        if ($this->sendCachedHeader) {
            $response = $response->withHeader('X-Redis-Cache-Hit', 'Hit');
        } else {
            $response = $response->withHeader('X-Redis-Cache-Hit', 'Miss');
        }

        // This is the end of the road. FIRE ZE RESPONSE!
        return $response;
    }

    /**
     * Responds gracefully with an error.
     *
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @param  string                                     $message   Response message to put in the error
     * @param  int                                        $errorCode Error code to set
     *
     * @return array
     */
    protected function respondWithError($message, $errorCode)
    {
        if ($this->statusCode === 200) {
            trigger_error(
                "This Error code 200 should never be sent. Please report this to the developer.",
                E_USER_WARNING
            );
        }

        // Pass to responder
        return $this->respondWithArray([
            'error' => [
                'code'      => $errorCode,
                'http_code' => $this->statusCode,
                'message'   => $message,
            ]
        ]);
    }

    /**
     * Generates a response with a 404 HTTP error and a given message.
     *
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @param  string                                     $message
     *
     * @return void
     */
    public function errorEmpty($message = 'No data / Empty')
    {
        return $this->setStatusCode(404)
                    ->respondWithError($message, self::CODE_EMPTY);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @param  string                                     $message
     *
     * @return void
     */
    public function errorForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(403)
                    ->respondWithError($message, self::CODE_FORBIDDEN);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @param  string                                     $message
     *
     * @return void
     */
    public function errorInternalError($message = 'Internal Error')
    {
        return $this->setStatusCode(500)
                    ->respondWithError($message, self::CODE_INTERNAL_ERROR);
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @param  string                                     $message
     *
     * @return void
     */
    public function errorNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(404)
                    ->respondWithError($message, self::CODE_NOT_FOUND);
    }

    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @param  string                                     $message
     *
     * @return void
     */
    public function errorUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(401)
                    ->respondWithError($message, self::CODE_UNAUTHORIZED);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param string                                     $message
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function errorWrongArgs($message = 'Wrong Arguments')
    {
        return $this->setStatusCode(400)
                    ->respondWithError($message, self::CODE_WRONG_ARGS);
    }

    /**
     * Reads any requested includes and adds them to the item / collection
     *
     * @param  Psr\Http\Message\ServerRequestInterface $request
     *
     * @return void
     */
    public function getIncludesFromRequest()
    {
        if (! empty($_GET['embed'])) {
            $this->fractal->parseIncludes($_GET['embed']);
        }
    }

    public function setAsCached()
    {
        $this->sendCachedHeader = true;
    }

    /**
     * Checks redis for a entry and returns it decoded if exists
     *
     * @param  string $type player|outfit
     * @param  string $id   ID of player or outfit
     *
     * @return string|boolean
     */
    public function checkRedis($store = 'api', $type, $id, $encodeType = 'array')
    {
        $redis = $this->getRedisDriver();
        $key = "ps2alerts:{$store}:{$type}:{$id}";

        if ($redis->exists($key)) {
            if ($encodeType === 'object') {
                $data = json_decode($redis->get($key));
            } else if ($encodeType === 'array') {
                $data = json_decode($redis->get($key), true);
            }

            $this->setAsCached();

            return $data;
        }

        return false;
    }

    /**
     * Stores the complete information in Redis
     *
     * @param  string  $namespace Split between cache and API
     * @param  string  $type
     * @param  string  $id
     * @param  string  $data
     * @param  string  $time Time in seconds to store data
     *
     * @return boolean
     */
    public function storeInRedis($namespace = 'api', $type, $id, $data, $time = false)
    {
        $redis = $this->getRedisDriver();
        $key = "ps2alerts:{$namespace}:{$type}:{$id}";

        $data = json_encode($data);

        // Check for errors #BRINGJSONEXCEPTIONS!
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception();
        }

        if (! $time) {
            $time = 3600 * 24; // 1 day
        }

        return $redis->setEx($key, $time, $data);
    }

    /**
     * Gets the Server or Zone filters and runs a check to make sure the request validates. Also formats the list
     * correctly for inclusion in query strings.
     *
     * @param  string                                     $queryString
     * @param  string                                     $mode
     *
     * @return string
     */
    public function getFiltersFromQueryString($queryString, $mode)
    {
        $filters = $this->getConfigItem($mode);
        $numericals = ['servers', 'zones'];
        $strings = ['factions', 'brackets'];

        if (! empty($queryString)) {
            $check = explode(',', $queryString);

            // Run a check on the IDs provided to make sure they're valid and no naughty things are being passed
            foreach ($check as $id) {
                // If the query string should contain only numbers
                if (in_array($mode, $numericals)) {
                    if (! is_numeric($id)) {
                        throw new InvalidArgumentException("Non numerical ID detected. Only numerical IDs are accepted with this request.");
                    }
                }
                if (in_array($mode, $strings)) {
                    if (is_numeric($id)) {
                        throw new InvalidArgumentException("Numerical input detected. Only string inputs are accepted with this request.");
                    }
                }

                if (! in_array($id, $filters)) {
                    throw new InvalidArgumentException("Unrecognized {$mode}. Please check the IDs you sent.");
                }
            }

            // Format into strings comma seperated for SQL
            if (in_array($mode, $strings)) {
                $queryString = "'" . implode("','", $check) . "'";
            }

            return $queryString;
        }

        $return = implode(',', $filters);

        if (in_array($mode, $strings)) {
            $return = "'" . implode("','", $filters) . "'";
        }

        // If no string was provided, returns all data encoded as a comma seperated string
        return $return;
    }
}
