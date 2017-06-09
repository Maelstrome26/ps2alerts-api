<?php

namespace Ps2alerts\Api\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

class HttpMessageServiceProvider extends AbstractServiceProvider
{
    protected $provides = [
        'Zend\Diactoros\Response',
        'Zend\Diactoros\Response\SapiEmitter',
        'Zend\Diactoros\ServerRequest'
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->share('Zend\Diactoros\Response', function() {
            $response = new Response();
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
            $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $response = $response->withHeader('Access-Control-Max-Age', '1000');
            $response = $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With');
            $response = $response->withHeader('x-best-faction', 'Vanu Sovereignty');
            return $response->withStatus(504);
        });

        $this->getContainer()->share('Zend\Diactoros\Response\SapiEmitter');

        $this->getContainer()->share('Zend\Diactoros\ServerRequest', function () {
            return ServerRequestFactory::fromGlobals();
        });
    }
}
