<?php

namespace Ps2alerts\Api\Controller\Endpoint\Profiles;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Repository\Metrics\OutfitTotalRepository;
use Ps2alerts\Api\Transformer\Profiles\OutfitProfileTransformer;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class OutfitProfileEndpointController extends AbstractEndpointController
{
    /**
     * Construct
     *
     * @param League\Fractal\Manager                                      $fractal
     * @param Ps2alerts\Api\Repository\Metrics\OutfitTotalRepository      $outfitTotalRepo
     * @param Ps2alerts\Api\Transformer\Profiles\OutfitProfileTransformer $outfitProfileTransformer
     */
    public function __construct(
        Manager                  $fractal,
        OutfitTotalRepository    $outfitTotalRepo,
        OutfitProfileTransformer $outfitProfileTransformer
    ) {
        $this->fractal                  = $fractal;
        $this->outfitTotalRepo          = $outfitTotalRepo;
        $this->outfitProfileTransformer = $outfitProfileTransformer;
    }

    /**
     * Gets a outfit
     *
     * @param  Psr\Http\Message\ServerRequestInterface  $request
     * @param  Psr\Http\Message\ResponseInterface $response
     * @param  array                                     $args
     *
     * @return array
     */
    public function getOutfit(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $outfit = $this->outfitTotalRepo->readSinglebyId($args['id']);

        return $this->respond(
            'item',
            $outfit,
            $this->outfitProfileTransformer
        );
    }
}
