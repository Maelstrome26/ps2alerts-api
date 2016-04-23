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
     * Gets a player's data
     *
     * @param  string $id Numerical player ID
     *
     * @return array
     */
    public function getPlayer(Request $request, Response $response, array $args)
    {
        $player = $this->getMetrics($args);

        // $player = $this->createItem($player, $this->playerSearchTransformer);

        var_dump($player);die;

        return $this->respond(
            'item',
            $player,
            $this->playerProfileTransformer,
            $request,
            $response
        );
    }

    public function getMetrics($args)
    {
        $player = $this->playerTotalRepo->readSinglebyId($args['id']);

        $metrics = [];

        // Get the data
        $metrics['alerts']   = $this->getPlayerAlerts($player);
        $metrics['weapons']  = $this->getPlayerWeapons($player);
        $metrics['vehicles'] = $this->getPlayerVehicles($player);

        $player['metrics']  = $this->parsePlayerMetrics($player);

        return $player;
    }

    public function getPlayerAlerts($player)
    {
        return $this->playerRepo->readAllByFields([
            'playerID' => $player['playerID']
        ]);
    }

    public function parsePlayerMetrics($player)
    {

    }
}
