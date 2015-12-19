<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\AbstractLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;

abstract class AbstractMetricsLoader extends AbstractLoader
{
    /**
     * Construct setting cache namespace
     */
    public function __construct()
    {
        $this->setCacheNamespace('Metrics:');
    }

    /**
     * Returns metrics for a particular result
     *
     * @param string
     *
     * @return array
     */
    public function readSingle($id)
    {
        $redisKey = "{$this->getCacheNamespace()}{$id}:{$this->getType()}";

        if ($this->checkRedis($redisKey)) {
            return $this->getFromRedis($redisKey);
        }

        $queryObject = new QueryObject;
        $queryObject->addWhere([
            'col'   => 'result',
            'value' => $id
        ]);

        return $this->cacheAndReturn(
            $this->repository->read($queryObject),
            $redisKey
        );
    }
}
