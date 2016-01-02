<?php

$container = new League\Container\Container;

// Register the request object singleton to be used later in the request cyncle
$container->singleton('Symfony\Component\HttpFoundation\Request', function () {
    return Symfony\Component\HttpFoundation\Request::createFromGlobals();
});

// Service Providers
$container->addServiceProvider('Ps2alerts\Api\ServiceProvider\ConfigServiceProvider');
$container->addServiceProvider('Ps2alerts\Api\ServiceProvider\DatabaseServiceProvider');
$container->addServiceProvider('Ps2alerts\Api\ServiceProvider\LogServiceProvider');
$container->addServiceProvider('Ps2alerts\Api\ServiceProvider\TemplateServiceProvider');
$container->addServiceProvider('Ps2alerts\Api\ServiceProvider\RedisServiceProvider');
$container->addServiceProvider('Ps2alerts\Api\ServiceProvider\UuidServiceProvider');

// Inflectors
$container->inflector('Ps2alerts\Api\Contract\ConfigAwareInterface')
          ->invokeMethod('setConfig', ['config']);
$container->inflector('Ps2alerts\Api\Contract\DatabaseAwareInterface')
          ->invokeMethod('setDatabaseDriver', ['Aura\Sql']);
$container->inflector('Ps2alerts\Api\Contract\LogAwareInterface')
          ->invokeMethod('setLogDriver', ['Monolog\Logger']);
$container->inflector('Ps2alerts\Api\Contract\TemplateAwareInterface')
          ->invokeMethod('setTemplateDriver', ['Twig_Environment']);
$container->inflector('Ps2alerts\Api\Contract\RedisAwareInterface')
          ->invokeMethod('setRedisDriver', ['redis']);
$container->inflector('Ps2alerts\Api\Contract\UuidAwareInterface')
          ->invokeMethod('setUuidDriver', ['Ramsey\Uuid\Uuid']);

$container->add('Ps2alerts\Api\Validator\AlertInputValidator');

$container->add('Ps2alerts\Api\Repository\AlertRepository');

$container->add('Ps2alerts\Api\Helper\DataFormatterHelper');

$container->add('Ps2alerts\Api\Loader\Statistics\AlertStatisticsLoader')
          ->withArgument('Ps2alerts\Api\Repository\AlertRepository')
          ->withArgument('Ps2alerts\Api\Validator\AlertInputValidator');

// Container Inflector
$container->inflector('League\Container\ContainerAwareInterface')
          ->invokeMethod('setContainer', [$container]);

return $container;
