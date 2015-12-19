<?php

namespace Ps2alerts\Api\Controller\Alerts;

use League\Route\Http\JsonResponse as Response;
use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\QueryObjects\QueryObject;
use Ps2alerts\Api\Loader\ResultLoader;
use Symfony\Component\HttpFoundation\Request;

class ResultsEndpointController extends EndpointBaseController
{
    protected $loader;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\ResultLoader $loader
     */
    public function __construct(ResultLoader $loader)
    {
        $this->loader = $loader;
        $this->setCacheNamespace('Alerts:');
        $this->loader->setLoaderCacheNamespace($this->getCacheNamespace());
    }

    /**
     * List recent alerts in the last 48 hours
     *
     * @return \League\Route\Http\JsonResponse
     */
    public function listRecent()
    {
        return new Response\Ok([
            'results' => $this->loader->readRecent()
        ]);
    }
}
