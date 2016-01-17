<?php

namespace Ps2alerts\Api\Controller\Endpoint;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Repository\AlertRepository;
use Ps2alerts\Api\Transformer\AlertTotalTransformer;
use Ps2alerts\Api\Transformer\AlertTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AlertEndpointController extends AbstractEndpointController
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

    /**
     * Returns a single alert's information
     *
     * @see AbstractEndpointController::respondWithItem
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     * @param  array                                     $args
     *
     * @return array
     */
    public function getSingle(Request $request, Response $response, array $args)
    {
        $alert = $this->repository->readSingleById($args['id']);

        if (empty($alert)) {
            return $this->errorEmpty($response);
        }

        return $this->respond('item', $alert, $this->transformer, $request, $response);
    }

    public function getActives(Request $request, Response $response)
    {
        $actives = $this->repository->readAllByFields(['InProgress', 1]);

        if (empty($actives)) {
            return $this->errorEmpty($response);
        }

        return $this->respond('collection', $actives, $this->transformer, $request, $response);
    }

    /**
     * Returns the victories of each faction and the totals
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     *
     * @return array
     */
    public function getVictories(Request $request, Response $response)
    {
        $counts = [
            'vs'          => $this->repository->readCountByFields(['ResultWinner' => 'VS', 'Valid' => 1]),
            'nc'          => $this->repository->readCountByFields(['ResultWinner' => 'NC', 'Valid' => 1]),
            'tr'          => $this->repository->readCountByFields(['ResultWinner' => 'TR', 'Valid' => 1]),
            'draw'        => $this->repository->readCountByFields(['ResultDraw' => 1, 'Valid' => 1]),
            'total'       => $this->repository->readCountByFields(['Valid' => 1])
        ];

        if (empty($counts['total'])) {
            return $this->errorEmpty($response);
        }

        return $this->respond('item', $counts, new AlertTotalTransformer, $request, $response);
    }

    /**
     * Returns the dominations of each faction and the totals
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     *
     * @return array
     */
    public function getDominations(Request $request, Response $response)
    {
        $counts = [
            'vs'          => $this->repository->readCountByFields(['ResultWinner' => 'VS', 'Valid' => 1, 'ResultDomination' => 1]),
            'nc'          => $this->repository->readCountByFields(['ResultWinner' => 'NC', 'Valid' => 1, 'ResultDomination' => 1]),
            'tr'          => $this->repository->readCountByFields(['ResultWinner' => 'TR', 'Valid' => 1, 'ResultDomination' => 1]),
            'total'       => $this->repository->readCountByFields(['Valid' => 1, 'ResultDomination' => 1])
        ];

        if (empty($counts['total'])) {
            return $this->errorEmpty($response);
        }

        return $this->respond('item', $counts, new AlertTotalTransformer, $request, $response);
    }
}
