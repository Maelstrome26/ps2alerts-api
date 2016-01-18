<?php

namespace Ps2alerts\Api\ServiceProvider;

use Aura\Sql\ExtendedPdo;
use League\Container\ServiceProvider;

class DatabaseDataServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'Database\Data'
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->singleton('Database\Data', function () {
            $config = $this->getContainer()->get('config')['database_data'];

            $pdo = new ExtendedPdo(
                "mysql:host={$config['host']};dbname={$config['schema']}",
                $config['user'],
                $config['password']
            );

            return $pdo;
        });
    }
}
