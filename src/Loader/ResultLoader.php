<?php

namespace Ps2alerts\Api\Loader;

use Ps2alerts\Api\QueryObjects\QueryObject;
use Ps2alerts\Api\Loader\AbstractLoader;
use Ps2alerts\Api\Repository\ResultRepository;

class ResultLoader extends AbstractLoader
{
    protected $repository;

    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Repository\ResultRepository $repository
     */
    public function __construct(ResultRepository $repository)
    {
        $this->repository = $repository;
        $this->setCacheNamespace('Alerts');
    }

    /**
     * Returns recent alerts
     *
     * @param  array $args Path Arguments
     *
     * @return array
     */
    public function readRecent(array $args)
    {
        $redisKey = "{$this->getCacheNamespace()}:Recent";

        $this->setCacheExpireTime(3600); // 1 hour

        $queryObject = new QueryObject;
        $queryObject->addWhere([
            'col'   => 'ResultStartTime',
            'op'    => '>',
            'value' => date('U', strtotime('-48 hours'))
        ]);

        if (! empty($args['serverID'])) {
            $redisKey .= ":{$args['serverID']}";
            $queryObject->addWhere([
                'col'   => 'ResultServer',
                'value' => $args['serverID']
            ]);
        }

        if (! empty($args['limit'])) {
            if ($args['limit'] > 50) {
                $args['limit'] = 50;
            }
            $redisKey .= "/{$args['limit']}";
            $queryObject->setLimit($args['limit']);
        }

        $queryObject->setOrderBy('ResultStartTime');
        $queryObject->setOrderByDirection('desc');

        return $this->cacheAndReturn(
            $this->repository->read($queryObject),
            $redisKey
        );
    }

    /**
     * Reads all currently active alerts
     *
     * @param  array $args
     *
     * @return array
     */
    public function readActive($args)
    {
        $queryObject = new QueryObject;
        $queryObject->addWhere([
            'col'   => 'InProgress',
            'value' => 1
        ]);

        if (! empty($args['serverID'])) {
            $queryObject->addWhere([
                'col'   => 'ResultServer',
                'value' => $args['serverID']
            ]);
        }

        return $this->repository->read($queryObject);
    }

    /**
     * Reads a single Alert
     *
     * @param  integer|string $id
     *
     * @return array
     */
    public function readSingle($id)
    {
        $redisKey = "{$this->getCacheNamespace()}{$id}";

        if ($this->checkRedis($redisKey)) {
            return $this->getFromRedis($redisKey);
        }

        $queryObject = new QueryObject;
        $queryObject->addWhere([
            'col'   => 'primary',
            'value' => $id
        ]);
        $queryObject->setDimension('single');

        $result = $this->repository->read($queryObject);

        if ($result['InProgress'] === '1') {
            $this->setCacheable(false);
        }

        return $this->cacheAndReturn($result, $redisKey);
    }
}
