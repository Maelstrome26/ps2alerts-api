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
    '/v2/alerts/{id}',
    'Ps2alerts\Api\Controller\Endpoint\AlertEndpointController::getSingle'
);

$route->post(
    '/v2/alerts/{id}/metric',
    'Ps2alerts\Api\Controller\Endpoint\AlertEndpointController::getSingleWithMetrics'
);

/**
 * Return the dispatcher to the app loader
 */
return $route->getDispatcher();
