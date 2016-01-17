<?php

use League\Container\Container;
use League\Container\ContainerInterface;
use League\Route\RouteCollection;

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
    '/v2/alerts/active',
    'Ps2alerts\Api\Controller\Endpoint\AlertEndpointController::getActives'
);

$route->get(
    '/v2/alerts/counts/victories',
    'Ps2alerts\Api\Controller\Endpoint\AlertEndpointController::getVictories'
);

$route->get(
    '/v2/alerts/counts/dominations',
    'Ps2alerts\Api\Controller\Endpoint\AlertEndpointController::getDominations'
);

$route->get(
    '/v2/alerts/{id}',
    'Ps2alerts\Api\Controller\Endpoint\AlertEndpointController::getSingle'
);

/**
 * Return the dispatcher to the app loader
 */
return $route->getDispatcher();
