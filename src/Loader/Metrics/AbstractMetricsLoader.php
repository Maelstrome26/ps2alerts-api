<?php

namespace Ps2alerts\Api\Loader\Metrics;

use Ps2alerts\Api\Loader\AbstractLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;

abstract class AbstractMetricsLoader extends AbstractLoader
{
    /**
     * Allows injection of where statements
     *
     * @var array
     */
    protected $metrics = [];

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

        if (! empty($this->getMetrics())) {
            foreach ($this->getMetrics() as $metric) {
                $hash = md5($metric['col'] . $metric['value']);
                $redisKey .= ":{$hash}";

                $queryObject->addWhere([
                    'col'   => $metric['col'],
                    'op'    => (isset($metric['op']) ? $metric['op'] : '='),
                    'value' => $metric['value']
                ]);
            }
        }

        return $this->cacheAndReturn(
            $this->repository->read($queryObject),
            $redisKey
        );
    }

    /**
     * Allows setting of metrics to filter
     *
     * @param array
     */
    public function setMetrics($metrics)
    {
        $this->metrics[] = $metrics;
    }

    /**
     * Pulls in metrics
     *
     * @return array
     */
    public function getMetrics()
    {
        return $this->metrics;
    }
}
