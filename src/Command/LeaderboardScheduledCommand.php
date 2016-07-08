<?php

namespace Ps2alerts\Api\Command;

use Ps2alerts\Api\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LeaderboardScheduledCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure(); // See BaseCommand.php
        $this->setName('Leaderboards:Check')
             ->setDescription('Checks all leaderboards for updates');

        global $container;
        $this->redis = $container->get('redis');
        $this->config = $container->get('config');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkPlayerLeaderboards($output);
    }

    public function checkPlayerLeaderboards(OutputInterface $output)
    {
        $output->writeln("Checking Player Leaderboards");
        $date = date('U');
        $deadline = $date - 21600; // 6 hours ago

        $servers = $this->config['servers'];
        $servers[] = '0';

        foreach($servers as $server) {
            $output->writeln("Checking Server {$server}");

            $key = "ps2alerts:api:leaderboards:status:{$server}";

            if (! $this->redis->exists($key)) {
                $output->writeln("Key doesn't exist for server: {$server}!!!");
                continue;
            }

            $data = json_decode($this->redis->get($key), true);

            if ($data['lastUpdated'] <= $deadline || $data['forceUpdate'] == 1) {
                $output->writeln("Executing update for server: {$server}");

                $command = "php {$this->config['commands_path']} Leaderboard:Players {$server}";
                exec($command);
            }
        }
    }
}
