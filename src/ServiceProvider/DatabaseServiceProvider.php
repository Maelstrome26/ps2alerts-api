<?php

namespace Ps2alerts\Api\ServiceProvider;

use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;
use League\Container\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'Database',
        'Aura\SqlQuery\QueryFactory'
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

        $this->getContainer()->add('Aura\SqlQuery\QueryFactory', function () {
            return new QueryFactory('mysql');
        });
    }
}
