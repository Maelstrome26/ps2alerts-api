<?php

namespace Ps2alerts\Api\Loader\Statistics;

use Ps2alerts\Api\Loader\Statistics\AbstractStatisticsLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;
use Ps2alerts\Api\Repository\AlertRepository;

class AlertStatisticsLoader extends AbstractStatisticsLoader
{
    /**
     * @var \Ps2alerts\Api\Repository\AlertRepository
     */
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\AlertRepository $repository
     */
    public function __construct(AlertRepository $repository)
    {
        $this->repository = $repository;
        $this->setCacheNamespace('Statistics');
        $this->setType('Alerts');
    }

    /**
     * Read total counts for alerts
     *
     * @param  array  $args
     *
     * @return array
     */
    public function readTotals($args)
    {
        $redisKey = "{$this->getCacheNamespace()}:{$this->getType()}:totals";

        $queryObject = new QueryObject;
        $queryObject->addSelect('COUNT(ResultID) AS COUNT');
        $queryObject->addWhere([
            'col'   => 'Valid',
            'value' => '1'
        ]);

        if (! empty($args['faction'])) {
            $faction = strtoupper($args['faction']);
            switch ($faction) {
                case 'VS':
                case 'NC':
                case 'TR':
                case 'DRAW':
                    $redisKey .= ":{$faction}";
                    $queryObject->addWhere([
                        'col'   => 'ResultWinner',
                        'value' => "'{$faction}'"
                    ]);
                    break;
            }
        }

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
