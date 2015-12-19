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
     * Returns cacheable property
     *
     * @return boolean
     */
    public function getCacheable()
    {
        return $this->cacheable;
    }

    /**
     * Require that the endpoints define a cache key namespace
     */
    public function setCacheNamespace($namespace)
    {
        $this->cacheNamespace = $namespace;
    }

    public function getCacheNamespace()
    {
        return $this->cacheNamespace;
    }

    /**
     * Returns a single entry
     *
     * @param  Request $request
     * @param  array   $args
     *
     * @return \League\Route\Http\JsonResponse
     */
    public function readSingle(Request $request, array $args)
    {
        $return = $this->loader->readSingle($args['resultID']);
        
        if (empty($return)) {
            return new Response\NoContent();
        }

        return new Response\Ok($return);
    }
}
