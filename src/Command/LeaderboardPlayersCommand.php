<?php

namespace Ps2alerts\Api\Command;

use Ps2alerts\Api\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LeaderboardPlayersCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure(); // See BaseCommand.php
        $this->setName('Leaderboards:Players')
             ->setDescription('Processes player leaderboards')
             ->addArgument(
                'server',
                InputArgument::REQUIRED
             );

        global $container;
        $this->redis = $container->get('redis');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $output->writeln("Running Player Leaderboards");

        $this->playerLeaderboards($input, $output);

        $end = microtime(true);
        $output->writeln("Processing took " . gmdate("H:i:s", ($end - $start)));
    }

    public function playerLeaderboards(InputInterface $input, OutputInterface $output)
    {
        $metrics = [
            'playerKills',
            'playerDeaths',
            'playerTeamkills',
            'playerSuicides',
            'headshots'
        ];
        $serverArg = $input->getArgument('server');

        // Allows server 0, meaning all servers but not to process every server
        if ($serverArg === 'all') {
            $servers = [0,1,10,13,17,25,1000,2000];
        } else {
            $servers = [$serverArg];
        }

        foreach($servers as $server) {
            foreach($metrics as $metric) {
                $this->markAsBeingUpdated($metric, $server);
            }
        }

        foreach($servers as $server) {
            foreach($metrics as $metric) {
                $count = 0;
                $limit = 10000;
                $ladderLimit = 10000;
                $pos = 1;

                $output->writeln("Running metric: {$metric} for server {$server}");

                $list = "ps2alerts:api:leaderboards:players:{$metric}:list-{$server}";

                // Delete the list for reprocessing
                if ($this->redis->exists($list)) {
                    $this->redis->del($list);
                }

                // Continue with loop until we don't have a count % modulus returning from the query
                while ($count < $ladderLimit && $count % $limit === 0 || $count === 0) {
                    $per = ($count / $ladderLimit) * 100;
                    $output->writeln("========= {$count} / {$ladderLimit} ({$per}%) =========");

                    $query = $this->auraFactory->newSelect();
                    $query->cols(['*']);
                    $query->from('ws_players_total');
                    if ($server != 0) {
                         $query->where("playerServer = ?", $server);
                    }
                    $query->orderBy([$metric . ' DESC']);
                    $query->limit($limit);
                    $query->offset($count);

                    $statement = $this->db->prepare($query->getStatement());
                    $statement->execute($query->getBindValues());

                    $count = $count + $statement->rowCount();

                    $output->writeln('Processing records...');

                    while ($player = $statement->fetch(\PDO::FETCH_OBJ)) {
                        $playerPosKey = "ps2alerts:api:leaderboards:players:pos:{$player->playerID}";

                        // If player record doesn't exist
                        if (! $this->redis->exists($playerPosKey)) {
                            $data = [
                                'updated' => [
                                    'daily'   => date('U'),
                                    'weekly'  => date('U'),
                                    'monthly' => date('U'),
                                ]
                            ];
                        } else {
                            $data = json_decode($this->redis->get($playerPosKey), true);
                        }

                        $deadlines = [
                            'daily',
                            'weekly',
                            'monthly'
                        ];

                        foreach($deadlines as $deadline) {
                            // If new record for the metric
                            if (empty($data[$server][$metric][$deadline])) {
                                $data[$server][$metric][$deadline]['old'] = 0;
                                $data[$server][$metric][$deadline]['new'] = 0;
                            }

                            // Flip new to old
                            if (! empty($data[$server][$metric][$deadline])) {
                                $data[$server][$metric][$deadline]['old'] = $data[$server][$metric][$deadline]['new'];
                            }

                            $dateObj = new \DateTime('now');
                            $interval = 'PT0H'; // Default to now.

                            if ($deadline === 'weekly') {
                                $interval = 'P7D';
                            } else if ($deadline === 'monthly') {
                                $interval = 'P1M';
                            }

                            $dateObj->sub(new \DateInterval($interval));
                            $deadlineTime = $dateObj->format('U');

                            if ($data['updated'][$deadline] <= $deadlineTime) {
                                // Update with new data
                                $data[$server][$metric][$deadline]['new'] = $pos;
                                $data['updated'][$deadline] = date('U');

                                $this->redis->set($playerPosKey, json_encode($data));
                            }
                        }

                        $this->redis->rpush($list, $player->playerID);
                        $pos++;
                    }
                }

                $this->markMetricAsComplete($metric, $server);
            }
            $this->markAsComplete($server);
        }
    }

    public function markAsBeingUpdated($metric, $server)
    {
        $key = "ps2alerts:api:leaderboards:status:{$server}";

        // Create the key if it doesn't exist for some reason (1st runs)
        if (! $this->redis->exists($key)) {
            $data = [
                'beingUpdated' => 1,
                'lastUpdated'  => date('U'),
                $metric        => date('U'),
            ];
        } else {
            $data = json_decode($this->redis->get($key), true);
            $newData = $data;
            $data['beingUpdated'] = 1;
            $data[$metric]        = date('U');
        }

        $this->redis->set($key, json_encode($data));
    }

    public function markMetricAsComplete($metric, $server)
    {
        $key = "ps2alerts:api:leaderboards:status:{$server}";

        $data = json_decode($this->redis->get($key), true);
        $data[$metric] = date('U');
        $this->redis->set($key, json_encode($data));
    }

    public function markAsComplete($server)
    {
        $key = "ps2alerts:api:leaderboards:status:{$server}";

        $data = json_decode($this->redis->get($key), true);

        $data['beingUpdated'] = 0;
        $data['lastUpdated'] = date('U');
        $this->redis->set($key, json_encode($data));
    }
}
