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

    /**
     * Gets total alerts based on provided parameters
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \League\Route\Http\JsonResponse
     */
    public function readTotals(Request $request)
    {
        $post = $request->request->all();

        $return = $this->loader->readTotals($post);

        if (empty($return)) {
            return new Response\NoContent();
        }

        return new Response\Ok($return);
    }
}
