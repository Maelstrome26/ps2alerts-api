<?php

return [
    'environment'    => $_ENV['ENV'],
    'base_url'       => $_ENV['BASE_URL'],
    'logger'         => $_ENV['LOGGER'],
    'slack_api'      => $_ENV['SLACK_API'],
    'slack_channel'  => $_ENV['SLACK_CHANNEL'],
    'slack_bot_name' => $_ENV['SLACK_BOT_NAME'],
    'database'     => [
        'host'     => $_ENV['DB_HOST'],
        'user'     => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASS'],
        'schema'   => $_ENV['DB_NAME']
    ],
    'database_data' => [
        'host'     => $_ENV['DB_HOST'],
        'user'     => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASS'],
        'schema'   => $_ENV['DB_NAME_DATA']
    ],
    'redis'        => [
        'enabled' => $_ENV['REDIS_ENABLED'],
        'host'    => $_ENV['REDIS_HOST'],
        'db'      => $_ENV['REDIS_DB']
    ],
    'servers'      => [1,10,13,17,25,1000,2000],
    'zones'        => [2,4,6,8],
    'factions'     => ['vs','nc','tr','draw'],
    'brackets'     => ['MOR','AFT','PRI'],
    'dbQueryDebug' => $_ENV['DB_QUERY_DEBUG']
];
