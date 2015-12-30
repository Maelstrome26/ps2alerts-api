<?php

namespace Ps2alerts\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use League\Route\Http\JsonResponse as Response;
use Ps2alerts\Api\QueryObjects\QueryObject;
use Ps2alerts\Api\Contract\LogAwareInterface;
use Ps2alerts\Api\Contract\LogAwareTrait;

abstract class EndpointBaseController implements
    LogAwareInterface
{
    use LogAwareTrait;

    protected $loader;

    /**
     * Returns a single entry
     *
     * @param  Symfony\Component\HttpFoundation\Request $request
     * @param  array                                    $args
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
