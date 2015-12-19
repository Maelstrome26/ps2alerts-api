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
    '/v2/alert',
    'Ps2alerts\Api\Controller\Alerts\ResultsEndpointController::listRecent',
    new RestfulStrategy
);

$route->get(
    '/v2/alert/{resultID}',
    'Ps2alerts\Api\Controller\Alerts\ResultsEndpointController::readSingle',
    new RestfulStrategy
);

// Metrics Routes
// - Map
$route->get(
    '/v2/metrics/map/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\MapMetricsEndpoint::readSingle',
    new RestfulStrategy
);

$route->get(
    '/v2/metrics/mapInitial/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\MapInitialMetricsEndpoint::readSingle',
    new RestfulStrategy
);
// - Outfits
$route->get(
    '/v2/metrics/outfits/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\OutfitMetricsEndpoint::readSingle',
    new RestfulStrategy
);

// Statistics routes
// - Outfit Totals
$route->get(
    '/v2/statistics/outfitTotals/top/{length}',
    'Ps2alerts\Api\Controller\Statistics\OutfitTotalsMetricsEndpoint::readTop',
    new RestfulStrategy
);

/**
 * Return the dispatcher to the app loader
 */
return $route->getDispatcher();
