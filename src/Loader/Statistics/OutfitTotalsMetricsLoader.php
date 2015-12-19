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
        $this->setType('OutfitTotals');
    }

    /**
     * Returns the top X of a particular statistic
     *
     * @return array
     */
    public function readTop($limit = 10, $metric = 'outfitKills')
    {
        $redisKey = "{$this->getCacheNamespace()}:{$this->getType()}:{$metric}:{$limit}";

        // Enforce a max limit
        if ($limit > 50) {
            $limit = 50;
        }

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
        $queryObject->setOrderBy($metric);
        $queryObject->setOrderByDirection('desc');
        $queryObject->setLimit($limit);

        return $this->cacheAndReturn(
            $this->repository->read($queryObject),
            $redisKey
        );
    }
}
