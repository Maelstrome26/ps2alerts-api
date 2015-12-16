<?php

use League\Container\Container;
use League\Container\ContainerInterface;
use League\Route\RouteCollection;
use League\Route\Strategy\RequestResponseStrategy;
use League\Route\Strategy\JsonStrategy;

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
    new JsonStrategy
);

$route->get(
    '/v2/alert/{resultID}',
    'Ps2alerts\Api\Controller\Alerts\ResultsEndpointController::readSingle',
    new JsonStrategy
);

/**
 * Return the dispatcher to the app loader
 */
return $route->getDispatcher();
