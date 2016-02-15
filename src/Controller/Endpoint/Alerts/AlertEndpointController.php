<?php

namespace Ps2alerts\Api\Controller\Endpoint\Alerts;

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
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     * @param  array                                     $args
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getSingle(Request $request, Response $response, array $args)
    {
        $alert = $this->repository->readSingleById($args['id']);

        if (empty($alert)) {
            return $this->errorEmpty($response);
        }

        return $this->respond('item', $alert, $this->transformer, $request, $response);
    }

    /**
     * Returns all currently running alerts
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getActives(Request $request, Response $response)
    {
        $actives = $this->repository->readAllByFields(['InProgress' => 1]);

        if (empty($actives)) {
            return $this->errorEmpty($response);
        }

        return $this->respond('collection', $actives, $this->transformer, $request, $response);
    }

    /**
     * Returns all alerts in historial order
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getHistoryByDate(Request $request, Response $response)
    {
        try {
            $servers  = $this->getFiltersFromQueryString($request->get('servers'), 'servers', $response);
            $zones    = $this->getFiltersFromQueryString($request->get('zones'), 'zones', $response);
            $factions = $this->getFiltersFromQueryString($request->get('factions'), 'factions', $response);
            $brackets = $this->getFiltersFromQueryString($request->get('brackets'), 'brackets', $response);
        } catch (InvalidArgumentException $e) {
            return $this->errorWrongArgs($response, $e->getMessage());
        }

        $dateFrom = $request->get('dateFrom');
        $dateTo   = $request->get('dateTo');
        $offset   = (int) $request->get('offset');
        $limit    = (int) $request->get('limit');

        // Set defaults if not supplied
        if ($offset === null || ! is_numeric($offset)) {
            $offset = 0;
        }

        if ($limit === null || ! is_numeric($limit)) {
            $limit = 25;
        }

        if ($dateFrom === null) {
            $dateFrom = date('Y-m-d H:i:s', strtotime('-48 hours'));
        }

        if ($dateTo === null) {
            $dateTo = date('Y-m-d H:i:s', strtotime('now'));
        }

        // Format the dates into UNIX timestamp for use with the DB
        $dateFrom = date('Y-m-d H:i:s', strtotime($dateFrom));
        $dateTo   = date('Y-m-d H:i:s', strtotime($dateTo));

        $query = $this->repository->newQuery();

        $query->cols(['*']);
        $query->where("`ResultServer` IN ({$servers})");
        $query->where("`ResultAlertCont` IN ({$zones})");
        $query->where("`ResultDateTime` > '{$dateFrom}'");
        $query->where("`ResultDateTime` < '{$dateTo}'");
        $query->where("`ResultWinner` IN ({$factions})");
        $query->where("`ResultTimeType` IN ({$brackets})");
        $query->orderBy(["`ResultEndTime` DESC"]);
        $query->limit($limit);
        $query->offset($offset);

        $history = $this->repository->readRaw($query->getStatement());

        return $this->respond('collection', $history, $this->transformer, $request, $response);
    }
}
