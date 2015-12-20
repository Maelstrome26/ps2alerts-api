<?php

namespace Ps2alerts\Api\Controller\Statistics;

use League\Route\Http\JsonResponse as Response;
use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Statistics\AlertStatisticsLoader;
use Symfony\Component\HttpFoundation\Request;

class AlertStatisticsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\Metrics\AlertStatisticsLoader $loader
     */
    public function __construct(AlertStatisticsLoader $loader)
    {
        $this->loader = $loader;
    }

    public function readTotals(Request $request, array $args)
    {
        $return = $this->loader->readTotals($args);

        if (empty($return)) {
            return new Response\NoContent();
        }

        return new Response\Ok($return);
    }
}
