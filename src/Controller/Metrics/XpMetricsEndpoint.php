<?php

namespace Ps2alerts\Api\Controller\Metrics;

use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Metrics\XpMetricsLoader;
use Symfony\Component\HttpFoundation\Request;

class XpMetricsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\Metrics\XpMetricsLoader $loader
     */
    public function __construct(XpMetricsLoader $loader)
    {
        $this->loader = $loader;
    }
}
