<?php

namespace Ps2alerts\Api\Controller\Metrics;

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
}
