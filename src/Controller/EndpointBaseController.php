<?php

namespace Ps2alerts\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use League\Route\Http\JsonResponse as Response;
use Ps2alerts\Api\QueryObjects\QueryObject;

abstract class EndpointBaseController
{
    protected $repository;

    protected $cacheable = true;

    protected $cacheNamespace;

    /**
     * Sets a flag whether or not the content should be cached
     *
     * @param boolean $toggle
     */
    public function setCacheable($toggle)
    {
        // If statement is here because can't figure out how to typehint a boolean...
        if ($toggle === true || $toggle === false) {
            $this->cacheable = $toggle;
        }
    }

    /**
     * Require that the endpoints define a cache key namespace
     */
    abstract public function setCacheNamespace();

    abstract public function readSingle(Request $request, array $args);
}
