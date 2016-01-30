<?php

namespace Ps2alerts\Api\Controller\Endpoint\Alerts;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Repository\AlertRepository;
use Ps2alerts\Api\Transformer\AlertTotalTransformer;
use Ps2alerts\Api\Transformer\AlertTransformer;
use Ps2alerts\Api\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AlertCountsEndpointController extends AbstractEndpointController
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
     * Returns the victories of each faction and the totals
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     *
     * @return array
     */
    public function getVictories(Request $request, Response $response)
    {
        return $this->getCountData($request, $response, 'victories');
    }

    /**
     * Returns the dominations of each faction and the totals
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response $response
     *
     * @return array
     */
    public function getDominations(Request $request, Response $response)
    {
        return $this->getCountData($request, $response, 'dominations');
    }

    /**
     * Gets the required count data and returns
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response $response
     * @param  string                                     $mode     The type of data we're getting (victory / domination)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCountData(Request $request, Response $response, $mode)
    {
        try {
            $servers = $this->getFiltersFromQueryString($request->get('servers'), 'servers', $response);
            $zones   = $this->getFiltersFromQueryString($request->get('zones'), 'zones', $response);
        } catch (InvalidArgumentException $e) {
            return $this->errorWrongArgs($response, $e->getMessage());
        }

        $counts = [];
        $serversExploded = explode(',', $servers);

        foreach ($serversExploded as $server) {
            $query = $this->repository->newQuery();

            $sql = '';

            foreach ($this->getConfigItem('factions') as $faction) {
                $factionAbv = strtoupper($faction);
                $sql .= "SUM(CASE WHEN `ResultWinner`='{$factionAbv}' ";
                $sql .= "AND `ResultServer` IN ({$server}) ";
                $sql .= "AND `ResultAlertCont` IN ({$zones}) ";

                if ($mode === 'dominations') {
                    $sql .= "AND `ResultDomination` = 1 ";
                }

                $sql .= "THEN 1 ELSE 0 END) {$faction}";

                if ($factionAbv !== 'DRAW') {
                    $sql .= ", ";
                }
            }

            $query->cols([$sql]);

            $data = $this->repository->readRaw($query->getStatement(), true);
            $data['total'] = array_sum($data);

            if ($mode === 'domination') {
                $data['draw'] = null; // For dominations, set it by default first
            }

            // Build each section of the final response using the transformer
            $counts['data'][$server] = $this->createItem($data, new AlertTotalTransformer);
        }

        // Return the now formatted array to the response
        return $this->respondWithArray($response, $counts);
    }

    public function getDateTotals(Request $request, Response $response)
    {
        try {
            $servers = $this->getFiltersFromQueryString($request->get('servers'), 'servers', $response);
            $zones   = $this->getFiltersFromQueryString($request->get('zones'), 'zones', $response);
        } catch (InvalidArgumentException $e) {
            return $this->errorWrongArgs($response, $e->getMessage());
        }

        
    }
}
