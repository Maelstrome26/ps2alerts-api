<?php

include __DIR__ . '/../vendor/autoload.php';

use League\Route\Http\Exception\NotFoundException;

// ENV loading
josegonzalez\Dotenv\Loader::load([
    'filepath' => __DIR__ . '/../.env',
    'toEnv'    => true
]);

// Container
$container = include __DIR__ . '/../src/container.php';

// Routes
$router = include __DIR__ . '/../src/routes.php';

$response = $container->get('Symfony\Component\HttpFoundation\Response');

// FIRE!!!
try {
    $response = $router->dispatch(
        $container->get('Symfony\Component\HttpFoundation\Request')->getMethod(),
        $container->get('Symfony\Component\HttpFoundation\Request')->getPathInfo()
    );
} catch (NotFoundException $e) {
    $response->setResponseCode(404)->setContent(
        $container->get('Twig_Environment')->render('404.html')
    );
}

$response->send();
