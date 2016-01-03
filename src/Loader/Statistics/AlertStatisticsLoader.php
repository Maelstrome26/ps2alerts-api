<?php

namespace Ps2alerts\Api\Loader\Statistics;

use Ps2alerts\Api\Loader\Statistics\AbstractStatisticsLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;
use Ps2alerts\Api\Repository\AlertRepository;
use Ps2alerts\Api\Loader\Metrics\MapMetricsLoader;
use Ps2alerts\Api\Validator\AlertInputValidator;
use Ps2alerts\Api\Helper\DataFormatterHelper;

class AlertStatisticsLoader extends AbstractStatisticsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\AlertRepository
     */
    protected $repository;

    /**
     * @var \Ps2alerts\Api\Loader\Metrics\MapMetricsLoader
     */
    protected $mapLoader;

    /**
     * @var \Ps2alerts\Api\Helper\DataFormatterHelper
     */
    protected $dataFormatter;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\AlertRepository       $repository
     * @param \Ps2alerts\Api\Loader\Metrics\MapMetricsLoader  $mapLoader
     * @param \Ps2alerts\Api\Helper\DataFormatter             $dataFormatter
     */
    public function __construct(
        AlertRepository     $repository,
        MapMetricsLoader    $mapLoader,
        DataFormatterHelper $dataFormatter
    ) {
        $this->repository    = $repository;
        $this->mapLoader     = $mapLoader;
        $this->dataFormatter = $dataFormatter;

        $this->setCacheNamespace('Statistics');
        $this->setType('Alerts');
    }

    /**
     * Read total counts for alerts
     *
     * @param  array $post
     *
     * @return array
     */
    public function readTotals(array $post)
    {
        $redisKey = "{$this->getCacheNamespace()}:{$this->getType()}:Totals";
        $redisKey = $this->appendRedisKey($post, $redisKey);
        $post     = $this->processPostVars($post);

        if ($this->checkRedis($redisKey)) {
            return $this->getFromRedis($redisKey);
        }

        $queryObject = new QueryObject;
        $queryObject = $this->setupQueryObject($queryObject, $post);

        $queryObject->addSelect('COUNT(ResultID) AS COUNT');

        if ($this->checkRedis($redisKey)) {
            return $this->getFromRedis($redisKey);
        }

        $this->setCacheExpireTime(900); // 15 mins

        return $this->cacheAndReturn(
            $this->repository->read($queryObject),
            $redisKey
        );
    }

    /**
     * Retrieves all zone totals and caches as required
     *
     * @return array
     */
    public function readZoneTotals()
    {
        $masterRedisKey = "{$this->getCacheNamespace()}:{$this->getType()}:Totals:Zones";

        if ($this->checkRedis($masterRedisKey)) {
            return $this->getFromRedis($masterRedisKey);
        }

        $servers  = [1,10,13,17,25,1000,2000];
        $zones    = [2,4,6,8];
        $factions = ['vs','nc','tr','draw'];

        $results = [];
        $this->setCacheExpireTime(3600); // 1 Hour

        // Dat loop yo
        foreach ($servers as $server) {
            foreach ($zones as $zone) {
                foreach ($factions as $faction) {
                    $results[$server][$zone][$faction] = $this->getZoneStats($server, $zone, $faction);
                }
            }
        }

        // Commit to Redis
        return $this->cacheAndReturn(
            $results,
            $masterRedisKey
        );
    }

    /**
     * Gets all information regarding zone victories out of the DB and caches as
     * required
     *
     * @see readZoneTotals()
     *
     * @param  integer $server
     * @param  integer $zone
     * @param  integer $faction
     *
     * @return array
     */
    public function getZoneStats($server, $zone, $faction)
    {
        $redisKey = "{$this->getCacheNamespace()}:{$this->getType()}:Totals:Zones";
        $redisKey .= ":{$server}:{$zone}:{$faction}";

        if ($this->checkRedis($redisKey)) {
            return $this->getFromRedis($redisKey);
        }

        // Fire a set of queries to build the object required
        $queryObject = new QueryObject;
        $queryObject->addSelect('COUNT(ResultID) AS COUNT');
        $queryObject->addWhere([
            'col'   => 'ResultServer',
            'value' => $server
        ]);
        $queryObject->addWhere([
            'col'   => 'ResultAlertCont',
            'value' => $zone
        ]);
        $queryObject->addWhere([
            'col'   => 'ResultWinner',
            'value' => $faction
        ]);

        // Commit to Redis
        return $this->cacheAndReturn(
            $this->repository->read($queryObject)[0]["COUNT"],
            $redisKey
        );
    }

    /**
     * Generates the data required for History Summaries
     *
     * @param  array $post
     *
     * @return array
     */
    public function readHistorySummary(array $post)
    {
        $redisKey = "{$this->getCacheNamespace()}:{$this->getType()}:HistorySummary";
        $redisKey = $this->appendRedisKey($post, $redisKey);
        $post     = $this->processPostVars($post);

        $queryObject = new QueryObject;
        $queryObject = $this->setupQueryObject($queryObject, $post);
        $queryObject->addSelect('FROM_UNIXTIME(ResultEndTime) AS ResultEndTime');
        $queryObject->addSelect('ResultWinner');
        $queryObject->addWhere([
            'col' => 'InProgress',
            'value' => 0
        ]);
        $queryObject->setLimit('unlimited');

        $minDate = '2014-10-29'; // Beginning of tracking
        $maxDate = date('Y-m-d'); // Today unless set

        // If there is a minimum date set
        if (! empty($post['wheres']['morethan']['ResultEndTime'])) {
            if (is_integer($post['wheres']['morethan']['ResultEndTime'])) {
                $minDate = date('Y-m-d', $post['wheres']['morethan']['ResultEndTime']);
            } else {
                $minDate = date('Y-m-d', strtotime($post['wheres']['morethan']['ResultEndTime']));
            }
        }

        // If there is a maximum date set
        if (! empty($post['wheres']['lessthan']['ResultEndTime'])) {
            if (is_integer($post['wheres']['lessthan']['ResultEndTime'])) {
                $maxDate = date('Y-m-d', $post['wheres']['lessthan']['ResultEndTime']);
            } else {
                $maxDate = date('Y-m-d', strtotime($post['wheres']['lessthan']['ResultEndTime']));
            }
        }

        $redisKey .= "/min-{$minDate}/max-{$maxDate}";

        if ($this->checkRedis($redisKey)) {
            return $this->getFromRedis($redisKey);
        }

        // Get the data to parse
        $alerts = $this->repository->read($queryObject);

        // Generate the range of dates
        $dates = $this->dataFormatter->createDateRangeArray($minDate, $maxDate);
        $dateRange = [];

        // Generates the victory totals for each date
        foreach ($dates as $date) {
            $dateRange[$date] = [
                'vs'   => 0,
                'nc'   => 0,
                'tr'   => 0,
                'draw' => 0
            ];
        }

        // Calculate metrics
        foreach ($alerts as $alert) {
            $date = date('Y-m-d', strtotime($alert['ResultEndTime']));
            $winner = strtolower($alert['ResultWinner']);

            $dateRange[$date][$winner]++;
        }

        // Commit to Redis
        return $this->cacheAndReturn(
            $dateRange,
            $redisKey
        );
    }

    /**
     * Reds the alert history based off two dates and other filters
     *
     * @param  array  $post [description]
     *
     * @return [type]       [description]
     */
    public function readAlertHistory(array $post)
    {
        $minDate = date('U', strtotime("-24 hours"));
        $maxDate = date('U');

        if (! empty($post['minDate'])) {
            $minDate = date('U', $post['minDate']);
        }

        if (! empty($post['maxDate'])) {
            $maxDate = date('U', $post['maxDate']);
        }

        $queryObject = new QueryObject;
        $queryObject->addWhere([
            'col'   => 'ResultEndTime',
            'op'    => '>',
            'value' => $minDate
        ]);
        $queryObject->addWhere([
            'col'   => 'ResultEndTime',
            'op'    => '<',
            'value' => $maxDate
        ]);

        if (! empty($post['server'])) {
            $queryObject->addWhere([
                'col'   => 'ResultServer',
                'value' => $post['server']
            ]);
        }

        if (! empty($post['faction'])) {
            $queryObject->addWhere([
                'col'   => 'ResultWinner',
                'value' => $post['faction']
            ]);
        }

        // Enforce a limit of 50 returns to prevent overload
        $queryObject->setLimit(50);

        $alerts = $this->repository->read($queryObject);

        // Grab the map information for each alert returned
        foreach ($alerts as $key => $alert) {
            $alertKey = "Alert:{$alert['ResultID']}";

            // Cache the alert if it's not been seen before
            if ($this->checkRedis($alertKey) === false) {
                $this->cacheAndReturn($alert, $alertKey);
            }

            // Cache the map result so we don't have to get it every time and set
            $alerts[$key]['map'] = $this->mapLoader->readLatest($alert['ResultID']);
        }

        return $alerts;
    }
}
