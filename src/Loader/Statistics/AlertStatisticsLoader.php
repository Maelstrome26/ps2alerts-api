<?php

namespace Ps2alerts\Api\Loader\Statistics;

use Ps2alerts\Api\Loader\Statistics\AbstractStatisticsLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;
use Ps2alerts\Api\Repository\AlertRepository;
use Ps2alerts\Api\Validator\AlertInputValidator;

class AlertStatisticsLoader extends AbstractStatisticsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\AlertRepository
     */
    protected $repository;

    /**
     * @var \Ps2alerts\Api\Validator\AlertInputValidator
     */
    protected $inputValidator;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\AlertRepository    $repository
     * @param \Ps2alerts\Api\Validator\AlertInputValidator $inputValidator
     */
    public function __construct(
        AlertRepository     $repository,
        AlertInputValidator $inputValidator
    ) {
        $this->repository     = $repository;
        $this->inputValidator = $inputValidator;

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
        $post = $this->processPostVars($post);

        $this->getLogDriver()->addDebug($redisKey);

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

        $this->getLogDriver()->addDebug($masterRedisKey);

        if ($this->checkRedis($masterRedisKey)) {
            $this->getLogDriver()->addDebug("Pulled the lot from Redis");
            return $this->getFromRedis($masterRedisKey);
        }

        $servers = [1,10,13,17,25,1000,1001,1002,1003,2000,2001,2002];
        $zones = [2,4,6,8];
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
    public function getZoneStats($server, $zone, $faction = null)
    {
        $redisKey = "{$this->getCacheNamespace()}:{$this->getType()}:Totals:Zones";

        if ($faction === null) {
            $redisKey .= ":{$server}:{$zone}";
        } else {
            $redisKey .= ":{$server}:{$zone}:{$faction}";
        }

        $this->getLogDriver()->addDebug($redisKey);

        if ($this->checkRedis($redisKey)) {
            $this->getLogDriver()->addDebug("CACHE PULL");
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
        if (! empty($faction)) {
            $queryObject->addWhere([
                'col'   => 'ResultWinner',
                'value' => $faction
            ]);
        }

        // Commit to Redis
        return $this->cacheAndReturn(
            $this->repository->read($queryObject)[0]["COUNT"],
            $redisKey
        );
    }
}
