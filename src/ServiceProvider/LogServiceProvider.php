<?php

namespace Ps2alerts\Api\ServiceProvider;

use League\Container\ServiceProvider;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SlackHandler;

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

            $config = $this->getContainer()->get('config');

            // Add slack for monitoring of critical errors
            if ($config['logger'] === 'slack') {
                $slackHandler = new SlackHandler(
                    $config['slack_api'],
                    $config['slack_channel'],
                    $config['slack_bot_name']
                );
                $slackHandler->setLevel(\Monolog\Logger::ERROR);

                $log->pushHandler($slackHandler, Logger::ERROR);
            }

            $log->pushHandler(
                new StreamHandler(__DIR__ . '/../../logs/app.log', Logger::DEBUG)
            );

            return $log;
        });
    }
}