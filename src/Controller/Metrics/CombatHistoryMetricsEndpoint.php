<?php

namespace Ps2alerts\Api\Controller\Metrics;

use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Metrics\CombatHistoryMetricsLoader;
use Symfony\Component\HttpFoundation\Request;

class CombatHistoryMetricsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\Metrics\CombatHistoryMetricsLoader $loader
     */
    public function __construct(CombatHistoryMetricsLoader $loader)
    {
        $this->loader = $loader;
    }
}
