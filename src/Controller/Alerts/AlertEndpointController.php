<?php

namespace Ps2alerts\Api\Controller\Alerts;

use League\Route\Http\JsonResponse as Response;
use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\QueryObjects\QueryObject;
use Ps2alerts\Api\Loader\AlertLoader;
use Symfony\Component\HttpFoundation\Request;

class AlertEndpointController extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\AlertLoader $loader
     */
    public function __construct(AlertLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * List recent alerts in the last 48 hours
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array                                     $args
     *
     * @return \League\Route\Http\JsonResponse
     */
    public function listLatest(Request $request, array $args)
    {
        $return = $this->loader->readRecent($args);

        if (empty($return)) {
            return new Response\NoContent();
        }

        return new Response\Ok($return);
    }

    /**
     * List alerts that are currently active
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array                                     $args
     *
     * @return \League\Route\Http\JsonResponse
     */
    public function listActive(Request $request, array $args)
    {
        $return = $this->loader->readActive($args);

        if (empty($return)) {
            return new Response\NoContent();
        }

        return new Response\Ok($return);
    }
}
