<?php

namespace Ps2alerts\Api\Controller\Endpoint;


use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Ps2alerts\Api\Contract\DatabaseAwareInterface;
use Ps2alerts\Api\Contract\DatabaseAwareTrait;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractEndpointController implements
    DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Contains the repository used for interfacing with the database
     *
     * @var Mixed | Ps2alerts\Api\Repository\AbstractEndpointRepository
     */
    protected $repository;

    protected $statusCode = 200;

    protected $fractal;

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
     * Builds an item response in Fractal then hands off to the responder
     *
     * @param  array $item                               The item to transform
     * @param  mixed $callback                           The Transformer to pass through to Fractal
     * @param  Symfony\Component\HttpFoundation\Response The client's response
     *
     * @return array                                     The formatted array
     */
    protected function respondWithItem($item, $callback, Response $response)
    {
        if (empty($item)) {
            return $this->errorEmpty($response);
        }

        $resource = new Item($item, $callback);

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($response, $rootScope->toArray());
    }

    /**
     * Builds a collection of items from Fractal then hands off to the responder
     *
     * @param  array $collection                         The collection to transform
     * @param  mixed $callback                           The Transformer to pass through to Fractal
     * @param  Symfony\Component\HttpFoundation\Response The client's response
     *
     * @return array                                     The formatted array
     */
    protected function respondWithCollection($collection, $callback, Response $response)
    {
        $resource = new Collection($collection, $callback);
        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($response, $rootScope->toArray());
    }

    /**
     * The final step where the formatted array is now sent back as a response in JSON form
     *
     * @param  Symfony\Component\HttpFoundation\Response $response
     * @param  array                                     $array    The formatted array
     *
     * @return Symfony\Component\HttpFoundation\Response           The final response
     */
    protected function respondWithArray(Response $response, array $array)
    {
        $response->setStatusCode($this->getStatusCode());
        $response->setContent(json_encode($array));
        $response->headers->set('Content-Type', 'application/json');

        // This is the end of the road. FIRE ZE RESPONSE!
        return $response;
    }

    /**
     * Responds gracefully with an error.
     *
     * @param  Response $response
     * @param  string   $message   Response message to put in the error
     * @param  int      $errorCode Error code to set
     *
     * @return array
     */
    protected function respondWithError(Response $response, $message, $errorCode)
    {
        if ($this->statusCode === 200) {
            trigger_error(
                "This Error code 200 should never be sent. Please report this to the developer.",
                E_USER_WARNING
            );
        }

        // Pass to responder
        return $this->respondWithArray($response, [
            'error' => [
                'code'      => $errorCode,
                'http_code' => $this->statusCode,
                'message'   => $message,
            ]
        ]);
    }

    /**
     * Generates a response with a 203 HTTP error and a given message.
     *
     * @param  Response $response
     * @param  string   $message
     *
     * @return Void
     */
    public function errorEmpty(Response $response, $message = 'No data / Empty')
    {
        return $this->setStatusCode(203)
                    ->respondWithError($response, $message, self::CODE_EMPTY);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @param  Response $response
     * @param  string   $message
     *
     * @return Void
     */
    public function errorForbidden(Response $response, $message = 'Forbidden')
    {
        return $this->setStatusCode(403)
                    ->respondWithError($response, $message, self::CODE_FORBIDDEN);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @param  Response $response
     * @param  string   $message
     *
     * @return Void
     */
    public function errorInternalError(Response $response, $message = 'Internal Error')
    {
        return $this->setStatusCode(500)
                    ->respondWithError($response, $message, self::CODE_INTERNAL_ERROR);
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @param  Response $response
     * @param  string   $message
     *
     * @return Void
     */
    public function errorNotFound(Response $response, $message = 'Resource Not Found')
    {
        return $this->setStatusCode(404)
                    ->respondWithError($response, $message, self::CODE_NOT_FOUND);
    }

    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @param  Response $response
     * @param  string   $message
     *
     * @return Void
     */
    public function errorUnauthorized(Response $response, $message = 'Unauthorized')
    {
        return $this->setStatusCode(401)
                    ->respondWithError($response, $message, self::CODE_UNAUTHORIZED);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorWrongArgs(Response $response, $message = 'Wrong Arguments')
    {
        return $this->setStatusCode(400)
                    ->respondWithError($response, $message, self::CODE_WRONG_ARGS);
    }

    /**
     * Reads a single record from the database then returns
     *
     * @param  string $id Primary key ID of record to return
     *
     * @return array
     */
    public function getSingleFromDb($id)
    {
        return $this->repository->readSingle($id);
    }
}
