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
            foreach($check as $id) {
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
            foreach($check as $id) {
                if (! in_array($id, $zones)) {
                    return $this->errorWrongArgs($response, 'Invalid Zone Arguments passed');
                }
            }

            $zones = $zonesQuery;
        } else {
            $zones = implode(',', $zones);
        }

        /* The marvelous query that is fired:
        SUM(CASE WHEN `ResultWinner`='VS' AND `ResultServer` IN (1,10,13,17,25,1000,2000) AND `ResultAlertCont` IN (2,4,6,8) THEN 1 ELSE 0 END) vs,
        */

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
}
