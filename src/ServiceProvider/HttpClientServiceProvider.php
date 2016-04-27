<?php

namespace Ps2alerts\Api\ServiceProvider;

use League\Container\ServiceProvider;
use GuzzleHttp\Client;

class HttpClientServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'GuzzleHttp\Client'
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->add('GuzzleHttp\Client', function () {
            return new Client([
                'timeout' => 10.0
            ]);
        });
    }
}
