<?php

namespace Ps2alerts\Api\Controller\Endpoint\Alerts;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\Alerts\AlertEndpointController;
use Ps2alerts\Api\Exception\InvalidArgumentException;
use Ps2alerts\Api\Repository\Metrics\CombatRepository;
use Ps2alerts\Api\Repository\Metrics\ClassRepository;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AlertCombatEndpointController extends AlertEndpointController
{
    public function __construct(
        classRepository $classRepository,
        combatRepository $combatRepository
    ) {
        $this->classRepository  = $classRepository;
        $this->combatRepository = $combatRepository;
    }

    /**
     * Retrieves combat totals on a global and per-server basis
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @return array
     */
    public function getCombatTotals(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            $servers = $this->getFiltersFromQueryString($_GET['servers'], 'servers');
            $zones   = $this->getFiltersFromQueryString($_GET['zones'], 'zones');
        } catch (InvalidArgumentException $e) {
            return $this->errorWrongArgs($e->getMessage());
        }

        $serversExploded = explode(',', $servers);
        $zonesExploded = explode(',', $zones);
        $zonesIn = $this->combatRepository->generateWhereInString($zonesExploded);

        $results = [];

        foreach ($serversExploded as $server) {
            $metrics = ['kills', 'deaths', 'teamkills', 'suicides', 'headshots'];
            $factions = ['vs', 'nc', 'tr'];

            $sums = [];
            foreach ($metrics as $metric) {
                foreach ($factions as $faction) {
                    $dbMetric = $metric . strtoupper($faction); // e.g. killsVS
                    $dataMetric = $metric . strtoupper($faction); // e.g. killsVS

                    // Handle teamkills inconsistency
                    if ($metric === 'teamkills') {
                        $dbMetric = 'teamKills' . strtoupper($faction);
                    }
                    $sums[] = "SUM(factions.{$dbMetric}) AS $dataMetric";
                }

                // Totals
                $dbMetric = 'total' . ucfirst($metric); // e.g. killsVS
                $dataMetric = 'total' . ucfirst($metric); // e.g. killsVS

                // Handle teamkills inconsistency
                if ($metric === 'teamkills') {
                    $dbMetric = 'totalTKs'; // Christ knows why
                }
                $sums[] = "SUM(factions.{$dbMetric}) AS $dataMetric";
            }

            $query = $this->combatRepository->newQuery('single', true);
            $query->cols($sums);
            $query->from('ws_factions AS factions');
            $query->join(
                'INNER',
                'ws_results AS results',
                "factions.resultID = results.ResultID"
            );
            $query->where('results.ResultServer = ?', $server);
            $query->where("results.ResultAlertCont IN {$zonesIn}");
            $query->where('results.Valid = ?', 1);

            $data = $this->combatRepository->fireStatementAndReturn($query, true);
            $dataArchive = $this->combatRepository->fireStatementAndReturn($query, true, false, true);

            $metrics = ['kills', 'deaths', 'teamkills', 'suicides', 'headshots'];
            $factions = ['vs', 'nc', 'tr'];

            // Merge the two arrays together
            foreach ($metrics as $metric) {
                foreach ($factions as $faction) {
                    $dbMetric = $metric . strtoupper($faction);
                    $mergedArray[$metric][$faction] = (int) $data[$dbMetric] + (int) $dataArchive[$dbMetric];
                }
            }

            // Tot up totals
            foreach ($metrics as $metric) {
                $dbMetric = 'total' . ucfirst($metric);
                $mergedArray['totals'][$metric] = (int) $data[$dbMetric] + (int) $dataArchive[$dbMetric];
                $results['all']['totals'][$metric] += $mergedArray['totals'][$metric];
            }

            $results[$server] = $mergedArray;
        }

        return $this->respondWithArray($results);
    }

    public function getClassTotals(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            $servers = $this->getFiltersFromQueryString($_GET['servers'], 'servers');
            $zones   = $this->getFiltersFromQueryString($_GET['zones'], 'zones');
        } catch (InvalidArgumentException $e) {
            return $this->errorWrongArgs($e->getMessage());
        }

        $serversExploded = explode(',', $servers);
        $zonesExploded = explode(',', $zones);
        $zonesIn = $this->combatRepository->generateWhereInString($zonesExploded);
        $classes = $this->getConfig()['classes'];

        // Build classes array
        foreach ($classes as $class) {
            $results['totals'][$class] = [
                'kills'     => 0,
                'deaths'    => 0,
                'teamkills' => 0,
                'suicides'  => 0
            ];
        }

        foreach ($serversExploded as $server) {
            $query = $this->combatRepository->newQuery('single', true);
            $query->cols([
                    'classID',
                    'results.ResultServer AS server',
                    'SUM(kills) AS kills',
                    'SUM(deaths) AS deaths',
                    'SUM(teamkills) AS teamkills',
                    'SUM(suicides) AS suicides'
            ]);
            $query->from('ws_classes AS classes');
            $query->join(
                'INNER',
                'ws_results AS results',
                "classes.resultID = results.ResultID"
            );
            $query->where('results.ResultServer = ?', $server);
            $query->where("results.ResultAlertCont IN {$zonesIn}");
            $query->where('results.Valid = ?', 1);
            $query->where('classID != ?', 0);
            $query->groupBy(['classID, server']);

            $data = $this->classRepository->fireStatementAndReturn($query, false, true);

            // Typecase into ints and increase totals
            $metrics = ['kills', 'deaths', 'teamkills', 'suicides'];
            foreach ($data as $row) {
                $row->classID   = (int) $row->classID;
                $row->server   = (int) $row->server;

                foreach ($metrics as $metric) {
                    $row->$metric = (int) $row->$metric;
                    $results[$row->server][$row->classID][$metric] += $row->$metric;
                    $results['totals'][$row->classID][$metric] += $row->$metric;

                    // Assign to class group
                    $classGroup = $this->findClassGrouping($row->classID);
                    $results['classGroups']['totals'][$classGroup][$metric] += $row->$metric;
                    $results['classGroups'][$row->server][$classGroup][$metric] += $row->$metric;
                }
            }
        }

        return $this->respondWithArray($results);
    }

    private function findClassGrouping($classID) {
        $classGroups = $this->getConfig()['classesGroups'];

        foreach ($classGroups as $group => $ids) {
            foreach ($ids as $id) {
                if ($classID === $id) {
                    return $group;
                }
            }
        }

        return false;
    }
}
