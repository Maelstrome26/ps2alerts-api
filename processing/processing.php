#!/usr/bin/env php

<?php

include __DIR__ . '/bootstrap.php';

include __DIR__ .'/leaderboards.php';

$task = $argv[1];

echo($task);

switch ($task) {
    case 'leaderboards';
        runLeaderboardProcessing($pdo, $redis);
        break;
}

function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}
