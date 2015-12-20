<?php

use League\Route\Strategy\RestfulStrategy;

// - Map
$route->get(
    '/v2/metrics/map/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\MapMetricsEndpoint::readSingle',
    new RestfulStrategy
);

$route->get(
    '/v2/metrics/map/{resultID}/latest',
    'Ps2alerts\Api\Controller\Metrics\MapMetricsEndpoint::readLatest',
    new RestfulStrategy
);

$route->get(
    '/v2/metrics/mapInitial/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\MapInitialMetricsEndpoint::readSingle',
    new RestfulStrategy
);

// - Outfits
$route->get(
    '/v2/metrics/outfit/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\OutfitMetricsEndpoint::readSingle',
    new RestfulStrategy
);

// - Populations
$route->get(
    '/v2/metrics/population/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\PopulationMetricsEndpoint::readSingle',
    new RestfulStrategy
);

// - Combat History
$route->get(
    '/v2/metrics/combathistory/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\CombatHistoryMetricsEndpoint::readSingle',
    new RestfulStrategy
);

// - Factions
$route->get(
    '/v2/metrics/faction/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\FactionMetricsEndpoint::readSingle',
    new RestfulStrategy
);

// - Players
$route->get(
    '/v2/metrics/player/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\PlayerMetricsEndpoint::readSingle',
    new RestfulStrategy
);

// - Vehicles
$route->get(
    '/v2/metrics/vehicle/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\VehicleMetricsEndpoint::readSingle',
    new RestfulStrategy
);
$route->get(
    '/v2/metrics/vehicle/{resultID}/{vehicleID}',
    'Ps2alerts\Api\Controller\Metrics\VehicleMetricsEndpoint::readSingleByMetric',
    new RestfulStrategy
);

// - Weapons
$route->get(
    '/v2/metrics/weapon/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\WeaponMetricsEndpoint::readSingle',
    new RestfulStrategy
);
$route->get(
    '/v2/metrics/weapon/{resultID}/{weaponID}',
    'Ps2alerts\Api\Controller\Metrics\WeaponMetricsEndpoint::readSingleByMetric',
    new RestfulStrategy
);

// - Classes
$route->get(
    '/v2/metrics/class/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\ClassMetricsEndpoint::readSingle',
    new RestfulStrategy
);
$route->get(
    '/v2/metrics/class/{resultID}/{classID}',
    'Ps2alerts\Api\Controller\Metrics\ClassMetricsEndpoint::readSingleByMetric',
    new RestfulStrategy
);

// - Xp
$route->get(
    '/v2/metrics/xp/{resultID}',
    'Ps2alerts\Api\Controller\Metrics\XpMetricsEndpoint::readSingle',
    new RestfulStrategy
);
