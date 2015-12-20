<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\AbstractLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;

abstract class AbstractMetricsLoader extends AbstractLoader
{
    /**
     * Returns metrics for a particular result
     *
     * @param string $id
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
