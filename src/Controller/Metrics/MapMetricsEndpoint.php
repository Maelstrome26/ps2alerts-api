<?php

namespace Ps2alerts\Api\Controller\Metrics;

use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Metrics\MapMetricsLoader;
use Symfony\Component\HttpFoundation\Request;

class MapMetricsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\MetricsLoader $loader
     */
    public function __construct(MapMetricsLoader $loader)
    {
        $this->loader = $loader;
        $this->setCacheNamespace('Metrics:');
        $this->loader->setLoaderCacheNamespace($this->getCacheNamespace());
    }
}
