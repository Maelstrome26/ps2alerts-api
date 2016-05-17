<?php

use Ps2alerts\Api\Command\TestCommand;
use Ps2alerts\Api\Command\DeleteAlertCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

// ENV loading
josegonzalez\Dotenv\Loader::load([
    'filepath' => __DIR__ . '/../.env',
    'toEnv'    => true
]);

$container = include __DIR__ . '/../src/container.php';

$application = new Application();
// List commands here
$application->add(new TestCommand());
$application->add(new DeleteAlertCommand());
$application->run();
