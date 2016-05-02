<?php

use ProgressBar\Manager;

function runLeaderboardProcessing($pdo, $redis)
{
    $start = microtime(true);
    echo "Running Player Leaderboards \n";
    playerLeaderboards($pdo, $redis);
    $end = microtime(true);
    echo "Processing took " . gmdate("H:i:s", ($end - $start)) . "\n";
}

function playerLeaderboards($pdo, $redis)
{
    $metrics = [
        'playerKills',
        'playerDeaths',
        'playerTeamkills',
        'playerSuicides',
        'headshots'
    ];
    $servers = [0,1,10,13,17,25,1000,2000];

    foreach($servers as $server) {
        foreach($metrics as $metric) {
            $count = 0;
            $limit = 10000;
            $ladderLimit = 10000;
            $pos = 1;

            echo "Running metric: {$metric}\n";

            $list = "ps2alerts:api:leaderboards:players:{$metric}:list-{$server}";

            // Delete the list for reprocessing
            if ($redis->exists($list)) {
                $redis->del($list);
            }

            if ($server !== 0) {
                 $serverQuery = " WHERE playerServer = {$server}";
            } else {
                $serverQuery = "";
            }

            // Continue with loop until we don't have a count % modulus returning from the query
            while ($count < $ladderLimit && $count % $limit === 0 || $count === 0) {
                $per = ($count / $ladderLimit) * 100;
                echo "========= {$count} / {$ladderLimit} ({$per}%) =========\n";
                $sql = "SELECT * FROM ws_players_total{$serverQuery} ORDER BY {$metric} DESC LIMIT {$limit} OFFSET {$count}";
                echo "Query: {$sql}\n";

                $statement = $pdo->prepare($sql);
                $statement->execute();

                $count = $count + $statement->rowCount();

                echo "MEMORY: " . convert(memory_get_usage(true)) . "\n";

                while ($player = $statement->fetch(PDO::FETCH_OBJ)) {
                    $playerPosKey = "ps2alerts:api:leaderboards:players:pos:{$player->playerID}";

                    // If player record doesn't exist
                    if (! $redis->exists($playerPosKey)) {
                        $data = [
                            'id'      => $player->playerID,
                            'updated' => date('U', strtotime('now'))
                        ];
                    } else {
                        $data = json_decode($redis->get($playerPosKey), true);
                    }

                    // For first time running
                    if (! empty($data[$server][$metric])) {
                        $data[$server][$metric]['old'] = $data[$server][$metric]['new'];
                    } else {
                        $data[$server][$metric]['old'] = 0;
                    }

                    $data[$server][$metric]['new'] = $pos;
                    $data['updated'] = date('U', strtotime('now'));

                    $redis->set($playerPosKey, json_encode($data));
                    $redis->rpush($list, $player->playerID);

                    if ($pos % 10000 === 0) {
                        echo "{$pos}\n";
                    }
                    $pos++;
                }
            }
        }
    }
}
