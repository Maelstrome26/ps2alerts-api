<?php

namespace Ps2alerts\Api\Controller\Endpoint;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Repository\AlertRepository;
use Ps2alerts\Api\Transformer\AlertTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AlertEndpointController extends AbstractEndpointController
{
    public function __construct(
        AlertRepository  $repository,
        AlertTransformer $transformer,
        Manager          $fractal
    ) {
        $this->repository  = $repository;
        $this->transformer = $transformer;
        $this->fractal     = $fractal;
    }

    /**
     * Returns a single alert's information
     *
     * @see AbstractEndpointController::respondWithItem
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     * @param  array
     *
     * @return array
     */
    public function getSingle(Request $request, Response $response, array $args)
    {
        $alert = $this->repository->readSingle($args['id']);

        if (empty($alert)) {
            return $this->errorEmpty($response);
        }

        return $this->respondWithItem($alert, $this->transformer, $response);
    }
}
