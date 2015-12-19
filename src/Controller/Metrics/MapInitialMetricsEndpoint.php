<?php

namespace Ps2alerts\Api\Controller\Metrics;

use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Metrics\MapInitialMetricsLoader;
use Symfony\Component\HttpFoundation\Request;

class MapInitialMetricsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\Metrics\MapInitialMetricsLoader $loader
     */
    public function __construct(MapInitialMetricsLoader $loader)
    {
        $this->loader = $loader;
    }
}
