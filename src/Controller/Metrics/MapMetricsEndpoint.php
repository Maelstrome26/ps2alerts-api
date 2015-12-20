<?php

namespace Ps2alerts\Api\Controller\Metrics;

use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Metrics\MapMetricsLoader;
use Symfony\Component\HttpFoundation\Request;
use League\Route\Http\JsonResponse as Response;

class MapMetricsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\Metrics\MapMetricsLoader $loader
     */
    public function __construct(MapMetricsLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Reads latest map result for an alert
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  array                                     $args
     *
     * @return \League\Route\Http\JsonResponse
     */
    public function readLatest(Request $request, array $args)
    {
        $return = $this->loader->readLatest($args['resultID']);

        if (empty($return)) {
            return new Response\NoContent();
        }

        return new Response\Ok($return);
    }
}
