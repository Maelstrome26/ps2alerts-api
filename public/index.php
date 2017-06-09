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

// FIRE!!!
try {
    $response = $router->dispatch(
        $container->get('Zend\Diactoros\ServerRequest'),
        $container->get('Zend\Diactoros\Response')
    );

    // Send the response to the client
    $container->get('Zend\Diactoros\Response\SapiEmitter')->emit($response);
} catch (NotFoundException $e) {
    $response = $container->get('Zend\Diactoros\Response');
    $response = $response->withStatus(404);
    $response->getBody()->write(
        $container->get('Twig_Environment')->render('404.html')
    );
} catch (\Exception $e) {
    $response = $container->get('Zend\Diactoros\Response');
    $response->getBody()->write(
        'An error occured! ' . $e->getMessage()
    );

    $logger = $container->get('Monolog\Logger');
    $logger->addDebug('Exception: ');
    $logger->addDebug($e->getMessage());

    $container->get('Zend\Diactoros\Response\SapiEmitter')->emit($response);
}
