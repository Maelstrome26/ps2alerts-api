<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\Metrics\AbstractMetricsLoader;
use Ps2alerts\Api\Repository\Metrics\MapRepository;
use Ps2alerts\Api\QueryObjects\QueryObject;

class MapMetricsLoader extends AbstractMetricsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\Metrics\MapRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\Metrics\MapRepository $repository
     */
    public function __construct(MapRepository $repository)
    {
        $this->repository = $repository;
        $this->setType('Map');
        $this->setCacheNamespace('Metrics:');
    }

    /**
     * Reads latest map result from an alert
     *
     * @param  string $id
     *
     * @return array
     */
    public function readLatest($id)
    {
        $redisKey = "{$this->getCacheNamespace()}{$id}:{$this->getType()}:latest";

        $queryObject = new QueryObject;
        $queryObject->addWhere([
            'col'   => 'result',
            'value' => $id
        ]);

        $queryObject->setOrderBy('result');
        $queryObject->setOrderByDirection('desc');
        $queryObject->setLimit(1);

        $this->setCacheExpireTime(60);

        return $this->cacheAndReturn(
            $this->repository->read($queryObject),
            $redisKey
        );
    }
}
