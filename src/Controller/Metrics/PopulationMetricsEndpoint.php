<?php

namespace Ps2alerts\Api\Controller\Metrics;

use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Metrics\PopulationMetricsLoader;
use Symfony\Component\HttpFoundation\Request;

class PopulationMetricsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\Metrics\PopulationMetricsLoader $loader
     */
    public function __construct(PopulationMetricsLoader $loader)
    {
        $this->loader = $loader;
    }
}
