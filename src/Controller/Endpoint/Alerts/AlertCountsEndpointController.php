<?php

namespace Ps2alerts\Api\Controller\Endpoint\Alerts;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Contract\ConfigAwareInterface;
use Ps2alerts\Api\Contract\ConfigAwareTrait;
use Ps2alerts\Api\Repository\AlertRepository;
use Ps2alerts\Api\Transformer\AlertTotalTransformer;
use Ps2alerts\Api\Transformer\AlertTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AlertCountsEndpointController extends AbstractEndpointController implements
    ConfigAwareInterface
{
    use ConfigAwareTrait;

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
        $serversQuery = $request->get('servers');
        $zonesQuery   = $request->get('zones');
        $servers      = $this->getConfigItem('servers');
        $zones        = $this->getConfigItem('zones');

        if (! empty($serversQuery)) {
            $check = explode(',', $serversQuery);

            // Run a check on the IDs provided to make sure they're valid and no naughty things are being passed
            foreach ($check as $id) {
                if (! in_array($id, $servers)) {
                    return $this->errorWrongArgs($response, 'Invalid Server Arguments passed');
                }
            }

            $servers = $serversQuery;
        } else {
            $servers = implode(',', $servers);
        }

        if (! empty($zonesQuery)) {
            $check = explode(',', $zonesQuery);

            // Run a check on the IDs provided to make sure they're valid and no naughty things are being passed
            foreach ($check as $id) {
                if (! in_array($id, $zones)) {
                    return $this->errorWrongArgs($response, 'Invalid Zone Arguments passed');
                }
            }

            $zones = $zonesQuery;
        } else {
            $zones = implode(',', $zones);
        }

        $counts = [];
        $serversExploded = explode(',', $servers);

        foreach ($serversExploded as $server) {
            $query = $this->repository->newQuery();

            $sql = $this->generateFactionCaseSql($server, $zones, $mode);

            $query->cols([$sql]);
            $data = $this->repository->readRaw($query->getStatement(), true);
            $data['total'] = array_sum($data);

            if ($mode === 'domination') {
                $data['draw'] = null; // Since domination draws are not possible, set it to null
            }

            // Build each section of the final response using the transformer
            $counts['data'][$server] = $this->createItem($data, new AlertTotalTransformer);
        }

        // Return the now formatted array to the response
        return $this->respondWithArray($response, $counts);
    }

    /**
     * Get Daily totals over a range of dates
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDailyTotals(Request $request, Response $response)
    {
        try {
            $servers = $this->getFiltersFromQueryString($request->get('servers'), 'servers', $response);
            $zones   = $this->getFiltersFromQueryString($request->get('zones'), 'zones', $response);
        } catch (InvalidArgumentException $e) {
            return $this->errorWrongArgs($response, $e->getMessage());
        }

        $data = [];
        $serversExploded = explode(',', $servers);

        $metrics = $this->getDailyMetrics($server, $zones);

        foreach ($metrics as $row) {
            $date = $row['dateIndex'];
            unset($row['dateIndex']);
            $row['total'] = array_sum($row);

            // Build each section of the final response using the transformer
            $data['data'][$date] = $this->createItem($row, new AlertTotalTransformer);
        }

        // Return the now formatted array to the response
        return $this->respondWithArray($response, $data);
    }

    /**
     * Get Daily totals over a range of dates broken down by server
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDailyTotalsByServer(Request $request, Response $response)
    {
        try {
            $servers = $this->getFiltersFromQueryString($request->get('servers'), 'servers', $response);
            $zones   = $this->getFiltersFromQueryString($request->get('zones'), 'zones', $response);
        } catch (InvalidArgumentException $e) {
            return $this->errorWrongArgs($response, $e->getMessage());
        }

        $data = [];
        $serversExploded = explode(',', $servers);

        foreach ($serversExploded as $server) {
            $metrics = $this->getDailyMetrics($server, $zones);

            foreach ($metrics as $row) {
                $date = $row['dateIndex'];
                unset($row['dateIndex']);
                $row['total'] = array_sum($row);

                // Build each section of the final response using the transformer
                $data['data'][$server][$date] = $this->createItem($row, new AlertTotalTransformer);
            }
        }

        // Return the now formatted array to the response
        return $this->respondWithArray($response, $data);
    }

    /**
     * Gets raw daily metrics which can be further processed
     *
     * @param  string $server
     * @param  string $zones
     *
     * @return array
     */
    public function getDailyMetrics($server, $zones)
    {
        $query = $this->repository->newQuery();

        $sql = $this->generateFactionCaseSql($server, $zones);
        $sql .= ', DATE(ResultDateTime) AS dateIndex';
        $query->cols([$sql]);

        $query->where('ResultDateTime IS NOT NULL');
        $query->groupBy(['dateIndex']);

        return $metrics = $this->repository->readRaw($query->getStatement());
    }

    /**
     * Generates the SELECT CASE statements required to filter down by Faction and Server
     *
     * @param  string $server
     * @param  string $zones
     * @param  string $mode
     *
     * @return string
     */
    public function generateFactionCaseSql($server = null, $zones = null, $mode = null)
    {
        $sql = '';

        foreach ($this->getConfigItem('factions') as $faction) {
            $factionAbv = strtoupper($faction);
            $sql .= "SUM(CASE WHEN `ResultWinner`='{$factionAbv}' ";
            if (! empty($server)) {
                $sql .= "AND `ResultServer` IN ({$server}) ";
            }

            if (! empty($zones)) {
                $sql .= "AND `ResultAlertCont` IN ({$zones}) ";
            }

            if ($mode === 'dominations') {
                $sql .= "AND `ResultDomination` = 1 ";
            }

            $sql .= "THEN 1 ELSE 0 END) {$faction}";

            if ($factionAbv !== 'DRAW') {
                $sql .= ", ";
            }
        }

        return $sql;
    }
}
