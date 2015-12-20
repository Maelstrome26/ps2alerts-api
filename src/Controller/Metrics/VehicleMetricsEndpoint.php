<?php

namespace Ps2alerts\Api\Controller\Metrics;

use League\Route\Http\JsonResponse as Response;
use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Metrics\VehicleMetricsLoader;
use Symfony\Component\HttpFoundation\Request;

class VehicleMetricsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\Metrics\VehicleMetricsLoader $loader
     */
    public function __construct(VehicleMetricsLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Returns a single entry by metric
     *
     * @param  Symfony\Component\HttpFoundation\Request $request
     * @param  array                                    $args
     *
     * @return \League\Route\Http\JsonResponse
     */
    public function readSingleByMetric(Request $request, array $args)
    {
        $this->loader->setMetrics([
            'col'   => 'vehicleID',
            'value' => $args['vehicleID']
        ]);

        $return = $this->loader->readSingle($args['resultID']);

        if (empty($return)) {
            return new Response\NoContent();
        }

        return new Response\Ok($return);
    }
}
