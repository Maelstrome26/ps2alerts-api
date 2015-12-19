<?php

namespace Ps2alerts\Api\Controller\Metrics;

use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Metrics\OutfitMetricsLoader;
use Symfony\Component\HttpFoundation\Request;

class OutfitMetricsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\MetricsLoader $loader
     */
    public function __construct(OutfitMetricsLoader $loader)
    {
        $this->loader = $loader;
    }
}
