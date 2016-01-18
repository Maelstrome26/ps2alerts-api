<?php

namespace Ps2alerts\Api\ServiceProvider;

use Aura\Sql\ExtendedPdo;
use League\Container\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'Database'
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->singleton('Database', function () {
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
