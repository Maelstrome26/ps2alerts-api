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
     * Returns top X entries
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  array                                     $args
     *
     * @return \League\Route\Http\JsonResponse
     */
    public function readStatistics(Request $request, array $args)
    {
        // Collect any POST variables
        $post = $request->request->all();

        $return = $this->loader->readStatistics($post);

        if (empty($return)) {
            return new Response\NoContent();
        }

        return new Response\Ok($return);
    }
}
