<?php

namespace Ps2alerts\Api\Controller\Statistics;

use League\Route\Http\JsonResponse as Response;
use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Statistics\OutfitTotalsMetricsLoader;
use Symfony\Component\HttpFoundation\Request;

class OutfitTotalsMetricsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\Metrics\OutfitTotalsMetricsLoader $loader
     */
    public function __construct(OutfitTotalsMetricsLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Returns a single entry
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  array   $args
     *
     * @return \League\Route\Http\JsonResponse
     */
    public function readTop(Request $request, array $args)
    {
        $return = $this->loader->readTop($args['length']);

        if (empty($return)) {
            return new Response\NoContent();
        }

        return new Response\Ok($return);
    }
}
