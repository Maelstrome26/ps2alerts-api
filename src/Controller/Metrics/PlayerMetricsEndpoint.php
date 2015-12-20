<?php

namespace Ps2alerts\Api\Controller\Metrics;

use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Metrics\PlayerMetricsLoader;
use Symfony\Component\HttpFoundation\Request;

class PlayerMetricsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\Metrics\PlayerMetricsLoader $loader
     */
    public function __construct(PlayerMetricsLoader $loader)
    {
        $this->loader = $loader;
    }
}
