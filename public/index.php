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

// Add headers to the response object so CORS is allowed
$response->headers->set('Access-Control-Allow-Origin', '*');
$response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
$response->headers->set('Access-Control-Max-Age', '1000');
$response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With');

$response->send();
