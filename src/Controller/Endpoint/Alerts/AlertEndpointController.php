<?php

namespace Ps2alerts\Api\Controller\Endpoint\Alerts;

use League\Fractal\Manager;
use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Repository\AlertRepository;
use Ps2alerts\Api\Transformer\AlertTotalTransformer;
use Ps2alerts\Api\Transformer\AlertTransformer;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AlertEndpointController extends AbstractEndpointController
{
    /**
     * Construct
     *
     * @param Ps2alerts\Api\Repository\AlertRepository   $repository
     * @param Ps2alerts\Api\Transformer\AlertTransformer $transformer
     * @param League\Fractal\Manager                     $fractal
     */
    public function __construct(
        AlertRepository  $repository,
        AlertTransformer $transformer,
        Manager          $fractal
    ) {
        $this->repository  = $repository;
        $this->transformer = $transformer;
        $this->fractal     = $fractal;
    }

    /**
     * Returns a single alert's information
     *
     * @param  Psr\Http\Message\ServerRequestInterface  $request
     * @param  Psr\Http\Message\ResponseInterface  $response
     * @param  array                                     $args
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getSingle(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $alert = $this->repository->readSingleById($args['id']);

        if (empty($alert)) {
            return $this->errorEmpty($response);
        }

        return $this->respond(
            'item',
            $alert,
            $this->transformer
        );
    }

    /**
     * Returns all currently running alerts
     *
     * @param  Psr\Http\Message\ServerRequestInterface  $request
     * @param  Psr\Http\Message\ResponseInterface $response
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getActives(ServerRequestInterface $request, ResponseInterface $response)
    {
        $actives = $this->repository->readAllByFields(['InProgress' => 1]);

        if (empty($actives)) {
            return $this->errorEmpty($response);
        }

        return $this->respond(
            'collection',
            $actives,
            $this->transformer,
            $request,
            $response
        );
    }

    /**
     * Returns all alerts in historial order
     *
     * @param  Psr\Http\Message\ServerRequestInterface  $request
     * @param  Psr\Http\Message\ResponseInterface $response
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getHistoryByDate(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            $servers  = $this->getFiltersFromQueryString($_GET['servers'], 'servers', $response);
            $zones    = $this->getFiltersFromQueryString($_GET['zones'], 'zones', $response);
            $factions = $this->getFiltersFromQueryString($_GET['factions'], 'factions', $response);
            $brackets = $this->getFiltersFromQueryString($_GET['brackets'], 'brackets', $response);
        } catch (InvalidArgumentException $e) {
            return $this->errorWrongArgs($response, $e->getMessage());
        }

        $dateFrom = $_GET['dateFrom'];
        $dateTo   = $_GET['dateTo'];
        $offset   = (int) $_GET['offset'];
        $limit    = (int) $_GET['limit'];

        // Set defaults if not supplied
        if ($offset === null || ! is_numeric($offset)) {
            $offset = 0;
        }

        if ($limit === null || ! is_numeric($limit)) {
            $limit = 25;
        }

        if ($dateFrom === null) {
            $dateFrom = date('Y-m-d H:i:s', strtotime('-48 hours'));
        }

        if ($dateTo === null) {
            $dateTo = date('Y-m-d H:i:s', strtotime('now'));
        }

        // Format the dates into UNIX timestamp for use with the DB
        $dateFrom = date('Y-m-d H:i:s', strtotime($dateFrom));
        $dateTo   = date('Y-m-d H:i:s', strtotime($dateTo));

        $query = $this->repository->newQuery();

        // @todo Look into doing bind properly
        // @too Look into doing WHERE IN statements with binds
        $query->cols(['*']);
        $query->where("ResultServer IN ({$servers})");
        $query->where("ResultAlertCont IN ({$zones})");
        $query->where('ResultDateTime > ?', $dateFrom);
        $query->where('ResultDateTime < ?', $dateTo);
        $query->where("ResultWinner IN ({$factions})");
        $query->where("ResultTimeType IN ({$brackets})");

        $query->orderBy(["ResultEndTime DESC"]);
        $query->limit($limit);
        $query->offset($offset);

        $history = $this->repository->fireStatementAndReturn($query);

        return $this->respond(
            'collection',
            $history,
            $this->transformer,
            $request,
            $response
        );
    }

    /**
     * Gets the Server or Zone filters and runs a check to make sure the request validates. Also formats the list
     * correctly for inclusion in query strings.
     *
     * @param  string                                     $queryString
     * @param  string                                     $mode
     * @param  \Psr\Http\Message\ResponseInterface $response
     *
     * @return string
     */
    public function getFiltersFromQueryString($queryString, $mode, $response)
    {
        $filters = $this->getConfigItem($mode);
        $numericals = ['servers', 'zones'];
        $strings = ['factions', 'brackets'];

        if (! empty($queryString)) {
            $check = explode(',', $queryString);

            // Run a check on the IDs provided to make sure they're valid and no naughty things are being passed
            foreach ($check as $id) {
                // If the query string should contain only numbers
                if (in_array($mode, $numericals)) {
                    if (! is_numeric($id)) {
                        throw new InvalidArgumentException("Non numerical ID detected. Only numerical IDs are accepted with this request.");
                    }
                }
                if (in_array($mode, $strings)) {
                    if (is_numeric($id)) {
                        throw new InvalidArgumentException("Numerical input detected. Only string inputs are accepted with this request.");
                    }
                }

                if (! in_array($id, $filters)) {
                    throw new InvalidArgumentException("Unrecognized {$mode}. Please check the IDs you sent.");
                }
            }

            // Format into strings comma seperated for SQL
            if (in_array($mode, $strings)) {
                $queryString = "'" . implode("','", $check) . "'";
            }

            return $queryString;
        }

        $return = implode(',', $filters);

        if (in_array($mode, $strings)) {
            $return = "'" . implode("','", $filters) . "'";
        }

        // If no string was provided, returns all data encoded as a comma seperated string
        return $return;
    }
}
