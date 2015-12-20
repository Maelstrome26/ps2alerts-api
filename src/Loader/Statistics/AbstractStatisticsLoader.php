<?php

namespace Ps2alerts\Api\Loader\Statistics;

use Ps2alerts\Api\Loader\AbstractLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;

abstract class AbstractStatisticsLoader extends AbstractLoader
{
    /**
     * Flags set for workarounds
     *
     * @var string
     */
    protected $flags;

    /**
     * Allows setting of workaround flags
     *
     * @param string $flag
     */
    public function setFlags($flag)
    {
        $this->flags = $flag;
    }

    /**
     * Retrieves workaround flags
     *
     * @return string
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @var string
     */
    protected $type;

    public function appendRedisKey($post, $redisKey)
    {
        if (! empty($post['wheres'])) {
            $whereMD5 = md5($post['wheres']);
            $redisKey .= "/{$whereMD5}";
        }
        if (! empty($post['orderBy'])) {
            $orderMD5 = md5($post['orderBy']);
            $redisKey .= "/{$orderMD5}";
        }
        if (! empty($post['limit'])) {
            // Enforce a max limit
            if ($post['limit'] > 50) {
                $post['limit'] = 50;
            }
        }

        if (empty($post['limit']) || ! isset($post['limit'])) {
            $post['limit'] = 10;
        }

        $redisKey .= "/{$post['limit']}";

        return $redisKey;
    }

    public function processPostVars($post)
    {
        if (! empty($post['wheres'])) {
            $return['wheres'] = json_decode($post['wheres'], true);
        }

        if (! empty($post['orderBy'])) {
            $return['orderBy'] = json_decode($post['orderBy'], true);
        }

        if (empty($post['limit']) || ! isset($post['limit'])) {
            $post['limit'] = 10;
        }

        if ($post['limit'] > 50) {
            $post['limit'] = 50;
        }

        $return['limit'] = $post['limit'];

        return $return;
    }

    /**
     * Returns the top X of a particular statistic
     *
     * @param array $post POST variables from the request
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

        if (! empty($this->getFlags())) {
            // If there are some funky things we have to do, set them.
            $queryObject->setFlags($this->getFlags());
        }

        return $this->cacheAndReturn(
            $this->repository->read($queryObject),
            $redisKey
        );
    }
}
