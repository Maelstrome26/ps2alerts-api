<?php

namespace Ps2alerts\Api\Controller\Endpoint\Profiles;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Exception\InvalidArgumentException;
use Ps2alerts\Api\Repository\Metrics\OutfitTotalRepository;
use Ps2alerts\Api\Repository\Metrics\PlayerTotalRepository;
use Ps2alerts\Api\Transformer\Profiles\OutfitProfileTransformer;
use Ps2alerts\Api\Transformer\Profiles\PlayerProfileTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileStatsEndpointController extends AbstractEndpointController
{
    /**
     * Construct
     *
     * @param League\Fractal\Manager                                   $fractal
     * @param Ps2alerts\Api\Repository\Metrics\OutfitTotalRepository   $outfitTotalRepo
     * @param Ps2alerts\Api\Transformer\Search\OutfitSearchTransformer $outfitSearchTransformer
     * @param Ps2alerts\Api\Repository\Metrics\PlayerTotalRepository   $playerTotalRepo
     * @param Ps2alerts\Api\Transformer\Search\PlayerSearchTransformer $playerSearchTransformer
     */
    public function __construct(
        Manager                  $fractal,
        OutfitTotalRepository    $outfitTotalRepo,
        PlayerTotalRepository    $playerTotalRepo,
        PlayerProfileTransformer $playerProfileTransformer,
        OutfitProfileTransformer $outfitProfileTransformer
    ) {
        $this->fractal                  = $fractal;
        $this->outfitRepository         = $outfitTotalRepo;
        $this->playerRepository         = $playerTotalRepo;
        $this->outfitProfileTransformer = $outfitProfileTransformer;
        $this->playerProfileTransformer = $playerProfileTransformer;
    }
}
