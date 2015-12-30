<?php

namespace Ps2alerts\Api\ServiceProvider;

use League\Container\ServiceProvider;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LogServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'Monolog\Logger',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->singleton('Monolog\Logger', function () {
            $log = new Logger('app');
            $log->pushHandler(
                new StreamHandler(__DIR__ . '/../../logs/app.log', Logger::DEBUG)
            );
            return $log;
        });
    }
}
