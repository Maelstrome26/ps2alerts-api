<?php

namespace Ps2alerts\Api\Controller\Endpoint\Alerts;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\Alerts\AlertEndpointController;
use Ps2alerts\Api\Exception\InvalidArgumentException;
use Ps2alerts\Api\Repository\Metrics\CombatRepository;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AlertCombatEndpointController extends AlertEndpointController
{
    public function __construct(
        combatRepository $combatRepository
    ) {
        $this->combatRepository  = $combatRepository;
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
        $zonesExploded = explode(',', $zones);
        $zonesIn = $this->combatRepository->generateWhereInString($zonesExploded);

        $totals = [];
        $totals['all']['totals'] = [
            'kills'     => 0,
            'deaths'    => 0,
            'teamkills' => 0,
            'suicides'  => 0
        ];

        foreach ($serversExploded as $server) {
            $query = $this->combatRepository->newQuery('single', true);
            $query->cols([
                'SUM(factions.killsVS) AS killsVS',
                'SUM(factions.killsNC) AS killsNC',
                'SUM(factions.killsTR) AS killsTR',
                'SUM(factions.deathsVS) AS deathsVS',
                'SUM(factions.deathsNC) AS deathsNC',
                'SUM(factions.deathsTR) AS deathsTR',
                'SUM(factions.teamKillsVS) AS teamkillsVS',
                'SUM(factions.teamKillsNC) AS teamkillsNC',
                'SUM(factions.teamKillsTR) AS teamkillsTR',
                'SUM(factions.suicidesVS) AS suicidesVS',
                'SUM(factions.suicidesNC) AS suicidesNC',
                'SUM(factions.suicidesTR) AS suicidesTR',
                'SUM(factions.headshotsVS) AS headshotsVS',
                'SUM(factions.headshotsNC) AS headshotsNC',
                'SUM(factions.headshotsTR) AS headshotsTR',
                'SUM(factions.totalKills) AS totalKills',
                'SUM(factions.totalDeaths) AS totalDeaths',
                'SUM(factions.totalTKs) AS totalTKs',
                'SUM(factions.totalSuicides) AS totalSuicides',
                'SUM(factions.totalHeadshots) AS totalHeadshots'
            ]);
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

            $mergedArray = [
                'kills' => [
                    'vs' => (int) $data['killsVS'] + (int) $dataArchive['killsVS'],
                    'nc' => (int) $data['killsNC'] + (int) $dataArchive['killsNC'],
                    'tr' => (int) $data['killsTR'] + (int) $dataArchive['killsTR']
                ],
                'deaths' => [
                    'vs' => (int) $data['deathsVS'] + (int) $dataArchive['deathsVS'],
                    'nc' => (int) $data['deathsNC'] + (int) $dataArchive['deathsNC'],
                    'tr' => (int) $data['deathsTR'] + (int) $dataArchive['deathsTR']
                ],
                'teamkills' => [
                    'vs' => (int) $data['teamkillsVS'] + (int) $dataArchive['teamkillsVS'],
                    'nc' => (int) $data['teamkillsNC'] + (int) $dataArchive['teamkillsNC'],
                    'tr' => (int) $data['teamkillsTR'] + (int) $dataArchive['teamkillsTR']
                ],
                'suicides' => [
                    'vs' => (int) $data['suicidesVS'] + (int) $dataArchive['suicidesVS'],
                    'nc' => (int) $data['suicidesNC'] + (int) $dataArchive['suicidesNC'],
                    'tr' => (int) $data['suicidesTR'] + (int) $dataArchive['suicidesTR']
                ],
                'headshots' => [
                    'vs' => (int) $data['headshotsVS'] + (int) $dataArchive['headshotsVS'],
                    'nc' => (int) $data['headshotsNC'] + (int) $dataArchive['headshotsNC'],
                    'tr' => (int) $data['headshotsTR'] + (int) $dataArchive['headshotsTR']
                ],
                'totals' => [
                    'kills'     => (int) $data['totalKills'] + (int) $dataArchive['totalKills'],
                    'deaths'    => (int) $data['totalDeaths'] + (int) $dataArchive['totalDeaths'],
                    'teamkills' => (int) $data['totalTKs'] + (int) $dataArchive['totalTKs'],
                    'suicides'  => (int) $data['totalSuicides'] + (int) $dataArchive['totalSuicides'],
                    'headshots'  => (int) $data['totalHeadshots'] + (int) $dataArchive['totalHeadshots']
                ]
            ];

            $totals['all'] = [
                'kills' => [
                    'vs' => $totals['all']['kills']['vs'] + $mergedArray['kills']['vs'],
                    'nc' => $totals['all']['kills']['nc'] + $mergedArray['kills']['nc'],
                    'tr' => $totals['all']['kills']['tr'] + $mergedArray['kills']['tr']
                ],
                'deaths' => [
                    'vs' => $totals['all']['deaths']['vs'] + $mergedArray['deaths']['vs'],
                    'nc' => $totals['all']['deaths']['nc'] + $mergedArray['deaths']['nc'],
                    'tr' => $totals['all']['deaths']['tr'] + $mergedArray['deaths']['tr']
                ],
                'teamkills' => [
                    'vs' => $totals['all']['teamkills']['vs'] + $mergedArray['teamkills']['vs'],
                    'nc' => $totals['all']['teamkills']['nc'] + $mergedArray['teamkills']['nc'],
                    'tr' => $totals['all']['teamkills']['tr'] + $mergedArray['teamkills']['tr']
                ],
                'suicides' => [
                    'vs' => $totals['all']['suicides']['vs'] + $mergedArray['suicides']['vs'],
                    'nc' => $totals['all']['suicides']['nc'] + $mergedArray['suicides']['nc'],
                    'tr' => $totals['all']['suicides']['tr'] + $mergedArray['suicides']['tr']
                ],
                'headshots' => [
                    'vs' => $totals['all']['headshots']['vs'] + $mergedArray['headshots']['vs'],
                    'nc' => $totals['all']['headshots']['nc'] + $mergedArray['headshots']['nc'],
                    'tr' => $totals['all']['headshots']['tr'] + $mergedArray['headshots']['tr']
                ],
                'totals' => [
                    'kills'     => $totals['all']['totals']['kills'] + $mergedArray['totals']['kills'],
                    'deaths'    => $totals['all']['totals']['deaths'] + $mergedArray['totals']['deaths'],
                    'teamkills' => $totals['all']['totals']['teamkills'] + $mergedArray['totals']['teamkills'],
                    'suicides'  => $totals['all']['totals']['suicides'] + $mergedArray['totals']['suicides'],
                    'headshots'  => $totals['all']['totals']['headshots'] + $mergedArray['totals']['headshots']
                ]
            ];

            $totals[$server] = $mergedArray;
        }

        return $this->respondWithArray($totals);
    }
}
