<?php

namespace Ps2alerts\Api\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Ps2alerts\Api\Contract\ConfigAwareInterface;
use Ps2alerts\Api\Contract\ConfigAwareTrait;
use Predis\Client;

class RedisServiceProvider extends AbstractServiceProvider implements
    ConfigAwareInterface
{
    use ConfigAwareTrait;
    /**
     * @var array
     */
    protected $provides = [
        'redis'
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->share('redis', function () {
            $redisConfig = $this->getContainer()->get('config')['redis'];

            $client = new Client([
                'host'     => $redisConfig['host'],
                'database' => intval($redisConfig['db']),
                'scheme'   => 'tcp'
            ]);

            return $client;
        });
    }
}
