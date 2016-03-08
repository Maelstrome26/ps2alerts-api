<?php

namespace Ps2alerts\Api\Controller\Endpoint\Profiles;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Exception\InvalidArgumentException;
use Ps2alerts\Api\Transformer\Profiles\PlayerSearchTransformer;
use Ps2alerts\Api\Repository\Metrics\PlayerTotalRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileStatsEndpointController extends AbstractEndpointController
{
    /**
     * Construct
     *
     * @param League\Fractal\Manager                     $fractal
     */
    public function __construct(
        Manager               $fractal,
        PlayerTotalRepository $playerTotalRepo,
        PlayerSearchTransformer $playerSearchTransformer
    ) {
        $this->fractal    = $fractal;
        $this->repository = $playerTotalRepo;
        $this->playerSearchTransformer = $playerSearchTransformer;
    }

    /**
     * Endpoint to return potential players based on search term
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * @param  Symfony\Component\HttpFoundation\Response $response
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getPlayersByTerm(Request $request, Response $response)
    {
        $name = $request->query->get('player');

        // If a valid player name we're searching on
        if ($this->parsePlayerName($name)) {
            $players = $this->searchForPlayer($name);

            if (! empty($players)) {
                return $this->respond('collection', $players, $this->playerSearchTransformer, $request, $response);
            }

            return $this->errorEmpty($response);
        }
    }

    public function searchForPlayer($term)
    {
        $query = $this->repository->newQuery();
        $query->cols(['playerID', 'playerName', 'playerFaction']);
        $query->where("playerName LIKE '%{$term}%'");

        return $this->repository->readRaw($query->getStatement());
    }

    public function getPlayerProfile($playerID)
    {

    }

    /**
     * Parses a player name and makes sure it's valid
     *
     * @param  String $name
     *
     * @return boolean
     */
    public function parsePlayerName($name)
    {
        if (empty($name)) {
            return $this->errorWrongArgs($response, 'Player name needs to be present.');
        }

        if (strlen($name > 24)) {
            return $this->errorWrongArgs($response, 'Player names cannot be longer than 24 characters.');
        }

        return true;
    }

    /**
     * Runs checks on the player ID
     *
     * @param  string $id
     *
     * @return boolean
     */
    public function parsePlayerID($id)
    {
        if (empty($id)) {
            return $this->errorWrongArgs($response, 'Player ID needs to be present.');
        }

        if (strlen($id > 19)) {
            return $this->errorWrongArgs($response, 'Player ID cannot be longer than 19 characters.');
        }

        if (! is_numeric($id)) {
            return $this->errorWrongArgs($response, 'Player ID must be numeric.');
        }

        return true;
    }
}
