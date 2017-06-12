<?php

namespace Ps2alerts\Api\Controller\Endpoint\Alerts;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\Alerts\AlertEndpointController;
use Ps2alerts\Api\Repository\AlertRepository;
use Ps2alerts\Api\Transformer\AlertTotalTransformer;
use Ps2alerts\Api\Transformer\AlertTransformer;
use Ps2alerts\Api\Exception\InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AlertCombatEndpointController extends AlertEndpointController
{
    /**
     * Construct
     *
     * @param Ps2alerts\Api\Repository\AlertRepository   $repository
     * @param Ps2alerts\Api\Transformer\AlertTransformer $transformer
     * @param League\Fractal\Manager                     $fractal
     */
    public function __construct(
        AlertRepository  $repository,
        AlertTransformer $transformer,
        Manager          $fractal
    ) {
        $this->repository  = $repository;
        $this->transformer = $transformer;
        $this->fractal     = $fractal;
    }

    public function getCombatTotals(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            $servers = $this->getFiltersFromQueryString($_GET['servers'], 'servers');
            $zones   = $this->getFiltersFromQueryString($_GET['zones'], 'zones');
        } catch (InvalidArgumentException $e) {
            return $this->errorWrongArgs($e->getMessage());
        }

        $serversExploded = explode(',', $servers);

        var_dump($serversExploded);die;
    }
}
