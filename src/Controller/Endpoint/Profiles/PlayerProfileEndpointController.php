<?php

namespace Ps2alerts\Api\Controller\Endpoint\Profiles;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Exception\InvalidArgumentException;
use Ps2alerts\Api\Repository\Metrics\PlayerTotalRepository;
use Ps2alerts\Api\Transformer\Profiles\PlayerProfileTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PlayerProfileEndpointController extends AbstractEndpointController
{
    /**
     * Construct
     *
     * @param League\Fractal\Manager                                      $fractal
     * @param Ps2alerts\Api\Repository\Metrics\PlayerTotalRepository      $playerTotalRepo
     * @param Ps2alerts\Api\Transformer\Profiles\PlayerProfileTransformer $playerProfileTransformer
     */
    public function __construct(
        Manager                  $fractal,
        PlayerTotalRepository    $playerTotalRepo,
        PlayerProfileTransformer $playerProfileTransformer
    ) {
        $this->fractal                  = $fractal;
        $this->playerTotalRepo          = $playerTotalRepo;
        $this->playerProfileTransformer = $playerProfileTransformer;
    }

    /**
     * Gets a player
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     * @param  array                                     $args
     *
     * @return array
     */
    public function getPlayer(Request $request, Response $response, array $args)
    {
        $player = $this->playerTotalRepo->readSinglebyId($args['id']);

        return $this->respond(
            'item',
            $player,
            $this->playerProfileTransformer,
            $request,
            $response
        );
    }
}
