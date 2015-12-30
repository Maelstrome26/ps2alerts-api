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
     * @param \Ps2alerts\Api\Loader\Statistics\AlertStatisticsLoader $loader
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

        $this->getLogDriver()->addDebug(json_encode($post));

        $return = $this->loader->readTotals($post);

        if (empty($return)) {
            return new Response\NoContent();
        }

        return new Response\Ok($return);
    }

    /**
     * Retrieves the zones totals
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \League\Route\Http\JsonResponse
     */
    public function readZoneTotals(Request $request)
    {
        $return = $this->loader->readZoneTotals();

        if (empty($return)) {
            return new Response\NoContent();
        }

        return new Response\Ok($return);
    }
}
