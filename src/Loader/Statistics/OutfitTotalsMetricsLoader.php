<?php

namespace Ps2alerts\Api\Loader\Statistics;

use Ps2alerts\Api\Loader\Statistics\AbstractStatisticsLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;
use Ps2alerts\Api\Repository\Statistics\OutfitTotalsRepository;

class OutfitTotalsMetricsLoader extends AbstractStatisticsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\Metrics\OutfitTotalsRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\Metrics\OutfitTotalsRepository $repository
     */
    public function __construct(OutfitTotalsRepository $repository)
    {
        $this->repository = $repository;
        $this->setCacheNamespace('Statistics');
        $this->setType('OutfitTotals');
    }

    /**
     * Returns the top X of a particular statistic
     *
     * @return array
     */
    public function readStatistics($post)
    {
        $redisKey = "{$this->getCacheNamespace()}:{$this->getType()}";
        $redisKey = $this->appendRedisKey($post, $redisKey);
        $post = $this->processPostVars($post);

        if ($this->checkRedis($redisKey)) {
            return $this->getFromRedis($redisKey);
        }

        $queryObject = new QueryObject;
        // Prevent the VS, NC and TR "no outfit" workaround
        $queryObject->addWhere([
            'col' => 'outfitID',
            'op' => '>',
            'value' => 0
        ]);

        foreach ($post['wheres'] as $key => $value) {
            $queryObject->addWhere([
                'col' => array_keys($value)[0],
                'value' => array_values($value)[0]
            ]);
        }

        if (! empty($post['orderBy'])) {
            $queryObject->setOrderBy(array_keys($post['orderBy'])[0]);
            $queryObject->setOrderByDirection(array_values($post['orderBy'])[0]);
        }

        $queryObject->setLimit($post['limit']);

        return $this->cacheAndReturn(
            $this->repository->read($queryObject),
            $redisKey
        );
    }
}
