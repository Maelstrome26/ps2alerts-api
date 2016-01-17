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
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     *
     * @return array
     */
    public function getDominations(Request $request, Response $response)
    {
        return $this->getCountData($request, $response, 'dominations');
    }

    public function getCountData(Request $request, Response $response, $mode)
    {
        $servers = $request->get('servers');

        if (! empty($servers) && $servers !== 'all') {
            $servers = explode(',', $servers);
        }

        if ($servers === 'all') {
            $servers = $this->getConfigItem('servers');
        }

        /*
        SELECT
	       sum(case when ResultWinner="VS" AND ResultServer= 1 then 1 else 0 end) VS
        FROM ws_results;
        */

        $query = $this->repository->newQuery();

        foreach ($servers as $server) {
            $filters = $this->buildCountFilters($mode, $server);
            $counts[$server]  = [
                'vs'    => $this->repository->readCountByFields($filters['vs']),
                'nc'    => $this->repository->readCountByFields($filters['nc']),
                'tr'    => $this->repository->readCountByFields($filters['tr']),
                'total' => $this->repository->readCountByFields($filters['total']),
                'draw'  => null
            ];

            if ($mode === 'victories') {
                $counts[$server]['draw'] = $this->repository->readCountByFields($filters['draw']);
            }
        }

        var_dump($counts);die;

        return $this->respond('item', $counts, new AlertTotalTransformer, $request, $response);
    }

    /**
     * Builds a set of filters based on input via query strings
     *
     * @param  Request $request
     * @param  string  $mode
     *
     * @return array
     */
    public function buildCountFilters($mode, $server = null)
    {
        $return = [];
        $fields = ['Valid' => 1];

        if ($mode === 'dominations') {
            $fields['ResultDomination'] = 1;
        }

        if (! empty($server)) {
            $fields['ResultServer'] = (int) $server;
        }

        $factions = $this->getConfigItem('factions');
        $factions[] = 'total';

        foreach ($factions as $faction) {
            if ($mode === 'dominations' && $faction === 'draw') {
                continue;
            }
            $return[$faction] = $fields;

            if ($faction !== 'total') {
                $return[$faction]['ResultWinner'] = strtoupper($faction);
            }
        }

        return $return;
    }

    public function getCountServers(Request $request, $mode)
    {
        $server = $request->get('server');

        if (! empty($server) && $server = 'all') {
            foreach ($this->getConfigItem('servers') as $server) {
                $filters = $this->buildCountFilters($request, $mode);
                $return[$server][$faction] = null;
            }
        }
    }
}
