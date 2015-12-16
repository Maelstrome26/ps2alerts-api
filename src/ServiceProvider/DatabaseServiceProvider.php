<?php

namespace Ps2alerts\Api\ServiceProvider;

use Aura\Sql\ExtendedPdo;
use League\Container\ServiceProvider\AbstractServiceProvider;

class DatabaseServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'Aura\Sql'
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->share('Aura\Sql', function () {
            $config = $this->getContainer()->get('config')['database'];

            $pdo = new ExtendedPdo(
                "mysql:host={$config['host']};dbname={$config['schema']}",
                $config['user'],
                $config['password']
            );

            return $pdo;
        });
    }
}
