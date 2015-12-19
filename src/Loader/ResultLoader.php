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
        $this->setCacheNamespace('Alerts:');
    }

    public function readRecent()
    {
        $queryObject = new QueryObject;
        $queryObject->addWhere([
            'col'   => 'ResultStartTime',
            'op'    => '>',
            'value' => date('U', strtotime('-7 days'))
        ]);
        $queryObject->setOrderBy('primary');
        $queryObject->setOrderByDirection('desc');

        return $this->repository->read($queryObject);
    }

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
