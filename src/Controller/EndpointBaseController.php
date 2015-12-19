<?php

namespace Ps2alerts\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use League\Route\Http\JsonResponse as Response;
use Ps2alerts\Api\QueryObjects\QueryObject;

abstract class EndpointBaseController
{
    protected $repository;

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
