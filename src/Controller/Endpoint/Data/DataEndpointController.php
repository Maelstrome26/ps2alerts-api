<?php

namespace Ps2alerts\Api\Controller\Endpoint\Data;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Transformer\DataTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DataEndpointController extends AbstractEndpointController
{
    /**
     * Construct
     *
     * @param Ps2alerts\Api\Transformer\DataTransformer $transformer
     * @param League\Fractal\Manager                     $fractal
     */
    public function __construct(
        DataTransformer $transformer,
        Manager         $fractal
    ) {
        $this->transformer = $transformer;
        $this->fractal     = $fractal;
    }

    /**
     * Gets supplemental data
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     * @param  array                                     $args
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getSupplementalData(Request $request, Response $response, array $args)
    {
        return $this->respond(
            'item',
            null,
            $this->transformer,
            $request,
            $response
        );
    }
}
