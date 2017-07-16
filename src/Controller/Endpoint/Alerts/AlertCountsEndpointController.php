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

class AlertCountsEndpointController extends AlertEndpointController
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
     * @return array
     */
    public function getVictories()
    {
        return $this->getCountData('victories');
    }

    /**
     * Returns the dominations of each faction and the totals
     *
     * @return array
     */
    public function getDominations()
    {
        return $this->getCountData('dominations');
    }

    /**
     * Gets the required count data and returns
     *
     * @param  string $mode The type of data we're getting (victory / domination)
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getCountData($mode)
    {
        try {
            $servers = $this->getFiltersFromQueryString($_GET['servers'], 'servers');
            $zones   = $this->getFiltersFromQueryString($_GET['zones'], 'zones');
        } catch (InvalidArgumentException $e) {
            return $this->errorWrongArgs($e->getMessage());
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
        return $this->respondWithArray($counts);
    }

    /**
     * Get Daily totals over a range of dates
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getDailyTotals()
    {
        try {
            $servers = $this->getFiltersFromQueryString($_GET['servers'], 'servers');
            $zones   = $this->getFiltersFromQueryString($_GET['zones'], 'zones');
        } catch (InvalidArgumentException $e) {
            return $this->errorWrongArgs($e->getMessage());
        }

        $data = [];

        $metrics = $this->getDailyMetrics($servers, $zones);

        foreach ($metrics as $row) {
            $date = $row['dateIndex'];
            unset($row['dateIndex']);
            $row['total'] = array_sum($row);

            // Build each section of the final response using the transformer
            $data['data'][$date] = $this->createItem($row, new AlertTotalTransformer);
        }

        // Return the now formatted array to the response
        return $this->respondWithArray($data);
    }

    /**
     * Get Daily totals over a range of dates broken down by server
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getDailyTotalsByServer()
    {
        try {
            $servers = $this->getFiltersFromQueryString($_GET['servers'], 'servers');
            $zones   = $this->getFiltersFromQueryString($_GET['zones'], 'zones');
        } catch (InvalidArgumentException $e) {
            return $this->errorWrongArgs($e->getMessage());
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
        return $this->respondWithArray($data);
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

            $sql .= "SUM(CASE WHEN `ResultWinner` = '{$factionAbv}' ";
            if (! empty($server)) {
                $sql .= "AND `ResultServer` IN ({$server}) ";
            }

            if (! empty($zones)) {
                $sql .= "AND `ResultAlertCont` IN ({$zones}) ";
            }

            if ($mode === 'dominations') {
                $sql .= "AND `ResultDomination` = 1 ";
            }

            $sql .= "THEN 1 ELSE 0 END) AS {$faction}";

            if ($factionAbv !== 'DRAW') {
                $sql .= ", ";
            }
        }

        return $sql;
    }
}
