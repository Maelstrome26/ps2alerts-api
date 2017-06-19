<?php

namespace Ps2alerts\Api\Controller\Endpoint\Search;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Exception\InvalidArgumentException;
use Ps2alerts\Api\Repository\Metrics\OutfitTotalRepository;
use Ps2alerts\Api\Repository\Metrics\PlayerTotalRepository;
use Ps2alerts\Api\Transformer\Search\OutfitSearchTransformer;
use Ps2alerts\Api\Transformer\Search\PlayerSearchTransformer;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class SearchEndpointController extends AbstractEndpointController
{
    /**
     * Construct
     *
     * @param League\Fractal\Manager                                   $fractal
     * @param Ps2alerts\Api\Transformer\Search\OutfitSearchTransformer $outfitSearchTransformer
     * @param Ps2alerts\Api\Repository\Metrics\OutfitTotalRepository   $outfitTotalRepo
     * @param Ps2alerts\Api\Transformer\Search\PlayerSearchTransformer $playerSearchTransformer
     * @param Ps2alerts\Api\Repository\Metrics\PlayerTotalRepository   $playerTotalRepo
     */
    public function __construct(
        Manager                 $fractal,
        OutfitTotalRepository   $outfitTotalRepo,
        PlayerTotalRepository   $playerTotalRepo,
        OutfitSearchTransformer $outfitSearchTransformer,
        PlayerSearchTransformer $playerSearchTransformer
    ) {
        $this->fractal                 = $fractal;
        $this->playerRepository        = $playerTotalRepo;
        $this->outfitRepository        = $outfitTotalRepo;
        $this->playerSearchTransformer = $playerSearchTransformer;
        $this->outfitSearchTransformer = $outfitSearchTransformer;
    }

    /**
     * Endpoint to return potential players based on search term
     *
     * @param  Psr\Http\Message\ServerRequestInterface  $request
     * @param  Psr\Http\Message\ResponseInterface $response
     * @param  array                                     $args
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getPlayersByTerm(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        // If a valid player name we're searching on
        if ($this->parsePlayerName($args['term'], $response)) {
            $players = $this->searchForPlayer($args['term']);

            if (! empty($players)) {
                return $this->respond(
                    'collection',
                    $players,
                    $this->playerSearchTransformer
                );
            }

            return $this->errorEmpty($response);
        }
    }

    /**
     * Endpoint to return potential players based on search term
     *
     * @param  Psr\Http\Message\ServerRequestInterface  $request
     * @param  Psr\Http\Message\ResponseInterface $response
     * @param  array                                     $args
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getOutfitsByTerm(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $name = urldecode($args['term']); // Spaces will have to URL encoded

        // If a valid outfit name we're searching on
        if ($this->parseOutfitName($name, $response)) {
            $outfits = $this->searchForOutfit($name);

            if (! empty($outfits)) {
                return $this->respond(
                    'collection',
                    $outfits,
                    $this->outfitSearchTransformer
                );
            }

            return $this->errorEmpty($response);
        }
    }

    /**
     * Takes a player name and searches for it
     *
     * @param  string $term
     *
     * @todo SQL injection prevention
     *
     * @return array
     */
    public function searchForPlayer($term)
    {
        $query = $this->playerRepository->newQuery();
        $query->cols(['*']);
        $query->where('playerName LIKE :term');
        $query->bindValue('term', "%{$term}%");

        return $this->playerRepository->fireStatementAndReturn($query);
    }

    /**
     * Takes a outfit name and searches for it
     *
     * @param  string $term
     *
     * @todo SQL injection prevention
     *
     * @return array
     */
    public function searchForOutfit($term)
    {
        $query = $this->outfitRepository->newQuery();
        $query->cols(['*']);
        $query->where("outfitTag LIKE :term");
        $query->bindValue('term', "%{$term}%");

        $data = $this->outfitRepository->fireStatementAndReturn($query);

        if (empty($data)) {
            $query = $this->outfitRepository->newQuery();
            $query->cols(['*']);
            $query->where("outfitName LIKE :term");
            $query->bindValue('term', "%{$term}%");

            $data = $this->outfitRepository->fireStatementAndReturn($query);
        }

        return $data;
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
            return $this->errorWrongArgs('Player name needs to be present.');
        }

        if (strlen($name) > 24) {
            return $this->errorWrongArgs('Player names cannot be longer than 24 characters.');
        }

        return true;
    }

    /**
     * Parses a outfit name and makes sure it's valid
     *
     * @param  String $name
     *
     * @return boolean
     */
    public function parseOutfitName($name)
    {
        if (empty($name)) {
            return $this->errorWrongArgs('Outfit name needs to be present.');
        }

        if (strlen($name) > 32) {
            return $this->errorWrongArgs('Outfit names cannot be longer than 32 characters.');
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
            return $this->errorWrongArgs('Player ID needs to be present.');
        }

        if (strlen($id > 19)) {
            return $this->errorWrongArgs('Player ID cannot be longer than 19 characters.');
        }

        if (! is_numeric($id)) {
            return $this->errorWrongArgs('Player ID must be numeric.');
        }

        return true;
    }
}
