<?php

namespace Ps2alerts\Api\Controller\Metrics;

use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Metrics\FactionMetricsLoader;
use Symfony\Component\HttpFoundation\Request;

class FactionMetricsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\Metrics\FactionMetricsLoader $loader
     */
    public function __construct(FactionMetricsLoader $loader)
    {
        $this->loader = $loader;
    }
}
