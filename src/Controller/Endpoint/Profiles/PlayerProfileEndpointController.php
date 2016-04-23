<?php

namespace Ps2alerts\Api\Controller\Endpoint\Profiles;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Exception\InvalidArgumentException;
use Ps2alerts\Api\Repository\Metrics\PlayerRepository;
use Ps2alerts\Api\Repository\Metrics\PlayerTotalRepository;
use Ps2alerts\Api\Transformer\Profiles\PlayerProfileTransformer;
use Ps2alerts\Api\Transformer\Search\PlayerSearchTransformer;
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
     * @param Ps2alerts\Api\Transformer\Search\PlayerSearchTransformer    $playerSearchTransformer
     */
    public function __construct(
        Manager                  $fractal,
        PlayerRepository         $playerRepo,
        PlayerTotalRepository    $playerTotalRepo,
        PlayerProfileTransformer $playerProfileTransformer,
        PlayerSearchTransformer  $playerSearchTransformer
    ) {
        $this->fractal                  = $fractal;
        $this->playerRepo               = $playerRepo;
        $this->playerTotalRepo          = $playerTotalRepo;
        $this->playerProfileTransformer = $playerProfileTransformer;
        $this->playerSearchTransformer  = $playerSearchTransformer;
    }

    /**
     * Gets a player
     *
     * @param  string $id
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
