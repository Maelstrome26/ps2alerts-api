<?php

namespace Ps2alerts\Api\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

class ConfigServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'config'
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->share('config', function () {
            return [
                'environment'    => $_ENV['ENV'],
                'base_url'       => $_ENV['BASE_URL'],
                'logger'         => $_ENV['LOGGER'],
                'slack_api'      => $_ENV['SLACK_API'],
                'slack_channel'  => $_ENV['SLACK_CHANNEL'],
                'slack_bot_name' => $_ENV['SLACK_BOT_NAME'],
                'database'     => [
                    'host'     => $_ENV['DB_HOST'],
                    'port'     => $_ENV['DB_PORT'],
                    'user'     => $_ENV['DB_USER'],
                    'password' => $_ENV['DB_PASS'],
                    'schema'   => $_ENV['DB_NAME']
                ],
                'database_data' => [
                    'host'     => $_ENV['DB_HOST'],
                    'port'     => $_ENV['DB_PORT'],
                    'user'     => $_ENV['DB_USER'],
                    'password' => $_ENV['DB_PASS'],
                    'schema'   => $_ENV['DB_NAME_DATA']
                ],
                'database_archive' => [
                    'host'     => $_ENV['DB_ARCHIVE_HOST'],
                    'port'     => $_ENV['DB_ARCHIVE_PORT'],
                    'user'     => $_ENV['DB_ARCHIVE_USER'],
                    'password' => $_ENV['DB_ARCHIVE_PASS'],
                    'schema'   => $_ENV['DB_ARCHIVE_NAME']
                ],
                'redis'        => [
                    'enabled'  => $_ENV['REDIS_ENABLED'],
                    'host'     => $_ENV['REDIS_HOST'],
                    'port'     => $_ENV['REDIS_PORT'],
                    'db'       => $_ENV['REDIS_DB'],
                ],
                'servers'           => [1, 10, 13, 17, 25, 1000, 2000],
                'zones'             => [2, 4, 6, 8],
                'classes'           => [1, 3, 4, 5, 6, 7, 8, 10, 11, 12, 13, 14, 15, 17, 18, 19, 20, 21],
                'classesGroups' => [
                    'infiltrator' => [1, 8, 15],
                    'la'          => [3, 10, 17],
                    'medic'       => [4, 11, 18],
                    'engineer'    => [5, 12, 19],
                    'ha'          => [6, 13, 20],
                    'max'         => [7, 14, 21]
                ],
                'classesFactions' => [
                    'nc' => [1, 3, 4, 5, 6, 7],
                    'tr' => [8, 10, 11, 12, 13, 14],
                    'vs' => [15, 17, 18, 19, 20, 21]
                ],
                'factions'          => ['vs', 'nc', 'tr', 'draw'],
                'brackets'          => ['MOR', 'AFT', 'PRI'],
                'db_query_debug'    => $_ENV['DB_QUERY_DEBUG'],
                'commands_path'     => $_ENV['COMMANDS_PATH'],
                'census_service_id' => $_ENV['CENSUS_SERVICE_ID']
            ];
        });
    }
}
