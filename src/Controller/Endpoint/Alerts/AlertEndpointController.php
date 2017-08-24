<?php

namespace Ps2alerts\Api\Controller\Endpoint\Alerts;

use Ps2alerts\Api\Controller\Endpoint\AbstractEndpointController;
use Ps2alerts\Api\Exception\InvalidArgumentException;
use Ps2alerts\Api\Repository\AlertRepository;
use Ps2alerts\Api\Transformer\AlertTransformer;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AlertEndpointController extends AbstractEndpointController
{
    /**
     * Construct
     *
     * @param AlertRepository  $repository
     * @param AlertTransformer $transformer
     */
    public function __construct(
        AlertRepository  $repository,
        AlertTransformer $transformer
    ) {

        $this->repository  = $repository;
        $this->transformer = $transformer;
    }

    /**
     * Returns a single alert's information
     *
     * @param  ServerRequestInterface  $request
     * @param  ResponseInterface       $response
     * @param  array                   $args
     *
     * @return ResponseInterface
     */
    public function getSingle(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $alert = $this->repository->readSingleById($args['id']);

        if (empty($alert)) {
            return $this->respondWithError('Alert not found', self::CODE_NOT_FOUND);
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
     * @param  ServerRequestInterface  $request
     * @param  ResponseInterface       $response
     *
     * @return ResponseInterface
     */
    public function getActives(ServerRequestInterface $request, ResponseInterface $response)
    {
        $actives = $this->repository->readAllByFields(['InProgress' => 1]);

        return $this->respond(
            'collection',
            $actives,
            $this->transformer
        );
    }

    /**
     * Returns all alerts in historical order
     *
     * @param  ServerRequestInterface  $request
     * @param  ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function getByDate(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            $servers  = $this->validateQueryStringArguments($_GET['servers'], 'servers');
            $zones    = $this->validateQueryStringArguments($_GET['zones'], 'zones');
            $factions = $this->validateQueryStringArguments($_GET['factions'], 'factions');
            $brackets = $this->validateQueryStringArguments($_GET['brackets'], 'brackets');
            $dates    = $this->validateQueryStringArguments($_GET['dates'], 'dates');
        } catch (InvalidArgumentException $e) {
            return $this->respondWithError($e->getMessage(), self::CODE_WRONG_ARGS);
        }

        $offset = (int) $_GET['offset'];
        $limit  = (int) $_GET['limit'];

        // Set defaults if not supplied
        if (empty($offset) || ! is_numeric($offset)) {
            $offset = 0;
        }

        if (empty($limit) || ! is_numeric($limit)) {
            $limit = 50;
        }

        // Check the date difference between two dates. we don't want to run queries for ALL OF ZE ALERTS NOW do we?!
        if (! empty($dates)) {
            try {
                $this->getDateValidationUtility()->validateTimeDifference($dates, 180); // Allow half a year
            } catch (InvalidArgumentException $e) {
                return $this->respondWithError($e->getMessage(), self::CODE_WRONG_ARGS);
            }

            $limit = null;
        }

        $query = $this->repository->newQuery();

        $brackets = $this->convertStringToArrayForAuraBinds($brackets);
        $brackets[] = 'UNK';

        $query->cols(['*']);
        $query->where('ResultServer IN (?)', $servers);
        $query->where('ResultAlertCont IN (?)', $zones);
        $query->where('ResultWinner IN (?)', $this->convertStringToArrayForAuraBinds($factions));
        $query->where('ResultTimeType IN (?)', $brackets);

        if (! empty($dates)) {
            $this->addDateRangeWhereClause($dates, $query);
        }

        $query->orderBy(["ResultEndTime DESC"]);
        if ($limit) {
            $query->limit($limit);
        }
        $query->offset($offset);

        $history = $this->repository->fireStatementAndReturn($query);

        return $this->respond(
            'collection',
            $history,
            $this->transformer
        );
    }
}
