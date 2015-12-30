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

        if ($this->checkRedis($redisKey)) {
            return $this->getFromRedis($redisKey);
        }

        $queryObject = $this->setupQueryObject($queryObject, $post);

        $queryObject = new QueryObject;
        $queryObject->addSelect('COUNT(ResultID) AS COUNT');
        $queryObject->addWhere([
            'col'   => 'Valid',
            'value' => '1'
        ]);

        if ($this->checkRedis($redisKey)) {
            return $this->getFromRedis($redisKey);
        }

        $this->setCacheExpireTime(900); // 15 mins

        return $this->cacheAndReturn(
            $this->repository->read($queryObject),
            $redisKey
        );
    }
}
