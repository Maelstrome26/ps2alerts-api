<?php

use League\Container\Container;
use League\Container\ContainerInterface;
use League\Route\RouteCollection;
use League\Route\Strategy\RequestResponseStrategy;
use League\Route\Strategy\RestfulStrategy;

// Load the route collection. If container is not ready, generate one here now.
$route = new RouteCollection(
    (isset($container) && $container instanceof ContainerInterface) ? $container : new Container
);

/**
 * Routes
 */
$route->get('/', 'Ps2alerts\Api\Controller\MainController::index');

// Alert Endpoint
$route->get(
    '/v2/alert/latest',
    'Ps2alerts\Api\Controller\Alerts\AlertEndpointController::listLatest',
    new RestfulStrategy
);
$route->get(
    '/v2/alert/latest/{serverID}',
    'Ps2alerts\Api\Controller\Alerts\AlertEndpointController::listLatest',
    new RestfulStrategy
);
$route->get(
    '/v2/alert/latest/{serverID}/{limit}',
    'Ps2alerts\Api\Controller\Alerts\AlertEndpointController::listLatest',
    new RestfulStrategy
);
$route->get(
    '/v2/alert/active',
    'Ps2alerts\Api\Controller\Alerts\AlertEndpointController::listActive',
    new RestfulStrategy
);
$route->get(
    '/v2/alert/active/{serverID}',
    'Ps2alerts\Api\Controller\Alerts\AlertEndpointController::listActive',
    new RestfulStrategy
);
$route->get(
    '/v2/alert/{resultID}',
    'Ps2alerts\Api\Controller\Alerts\AlertEndpointController::readSingle',
    new RestfulStrategy
);

// Metrics Routes
include(__DIR__ . '/routes-metrics.php');

// Statistics routes

// - Alert Statistics

// - Outfit Totals
$route->post(
    '/v2/statistics/outfitTotals',
    'Ps2alerts\Api\Controller\Statistics\OutfitTotalsStatisticsEndpoint::readStatistics',
    new RestfulStrategy
);

/**
 * Return the dispatcher to the app loader
 */
return $route->getDispatcher();
