#!/usr/bin/env php

<?php

include __DIR__ . '/bootstrap.php';

$task = $argv[1];

echo("RUNNING TASK: {$task} \n");

switch ($task) {
    case 'leaderboards':
        runLeaderboardProcessing($pdo, $redis);
        break;
    case 'deleteAlert':
        deleteAlert($pdo);
        break;
}
