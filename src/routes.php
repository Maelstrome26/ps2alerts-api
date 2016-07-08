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
    'Ps2alerts\Api\Controller\Endpoint\Alerts\AlertEndpointController::getActives'
);

$route->get(
    '/v2/alerts/counts/victories',
    'Ps2alerts\Api\Controller\Endpoint\Alerts\AlertCountsEndpointController::getVictories'
);

$route->get(
    '/v2/alerts/counts/dominations',
    'Ps2alerts\Api\Controller\Endpoint\Alerts\AlertCountsEndpointController::getDominations'
);

$route->get(
    '/v2/alerts/counts/daily',
    'Ps2alerts\Api\Controller\Endpoint\Alerts\AlertCountsEndpointController::getDailyTotals'
);

$route->get(
    '/v2/alerts/counts/dailyByServer',
    'Ps2alerts\Api\Controller\Endpoint\Alerts\AlertCountsEndpointController::getDailyTotalsByServer'
);

$route->get(
    '/v2/alerts/history',
    'Ps2alerts\Api\Controller\Endpoint\Alerts\AlertEndpointController::getHistoryByDate'
);

$route->get(
    '/v2/alerts/{id}',
    'Ps2alerts\Api\Controller\Endpoint\Alerts\AlertEndpointController::getSingle'
);

$route->get(
    '/v2/data',
    'Ps2alerts\Api\Controller\Endpoint\Data\DataEndpointController::getSupplementalData'
);

/**
 * Searches
 */
$route->get(
    '/v2/search/player/{term}',
    'Ps2alerts\Api\Controller\Endpoint\Search\SearchEndpointController::getPlayersByTerm'
);

$route->get(
    '/v2/search/outfit/{term}',
    'Ps2alerts\Api\Controller\Endpoint\Search\SearchEndpointController::getOutfitsByTerm'
);

/**
 * Profiles
 */
$route->get(
    '/v2/profiles/player/{id}',
    'Ps2alerts\Api\Controller\Endpoint\Profiles\PlayerProfileEndpointController::getPlayer'
);

$route->get(
    '/v2/profiles/outfit/{id}',
    'Ps2alerts\Api\Controller\Endpoint\Profiles\OutfitProfileEndpointController::getOutfit'
);

/**
 * Leaderboards
 */
$route->get(
    '/v2/leaderboards/players',
    'Ps2alerts\Api\Controller\Endpoint\Leaderboards\LeaderboardEndpointController::players'
);

$route->get(
    '/v2/leaderboards/outfits',
    'Ps2alerts\Api\Controller\Endpoint\Leaderboards\LeaderboardEndpointController::outfits'
);

$route->get(
    '/v2/leaderboards/weapons',
    'Ps2alerts\Api\Controller\Endpoint\Leaderboards\LeaderboardEndpointController::weapons'
);

$route->get(
    '/v2/leaderboards/update',
    'Ps2alerts\Api\Controller\Endpoint\Leaderboards\LeaderboardEndpointController::update'
);

/**
 * Return the dispatcher to the app loader
 */
return $route->getDispatcher();
