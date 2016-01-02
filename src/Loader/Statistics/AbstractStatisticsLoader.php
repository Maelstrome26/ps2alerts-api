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
        $queryObject = $this->setupQueryObject($queryObject, $post);

        return $this->cacheAndReturn(
            $this->repository->read($queryObject),
            $redisKey
        );
    }

    /**
     * Build a redis key based off inputs provided by the POST request
     *
     * @param  array  $post
     * @param  string $redisKey Redis Key to append to
     *
     * @return string
     */
    public function appendRedisKey($post, $redisKey)
    {
        if (! empty($post['selects'])) {
            $whereMD5 = md5($post['selects']);
            $redisKey .= "/select:{$whereMD5}";
        }

        if (! empty($post['wheres'])) {
            $whereMD5 = md5($post['wheres']);
            $redisKey .= "/where:{$whereMD5}";
        }

        if (! empty($post['whereIns'])) {
            $whereInMD5 = md5($post['whereIns']);
            $redisKey .= "/whereIn:{$whereInMD5}";
        }

        if (! empty($post['orderBy'])) {
            $orderMD5 = md5($post['orderBy']);
            $redisKey .= "/order:{$orderMD5}";
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

        $redisKey .= "/limit{$post['limit']}";

        return $redisKey;
    }

    /**
     * De-encode the POST vars for use
     *
     * @param  array $post
     *
     * @return array
     */
    public function processPostVars($post)
    {
        if (! empty($post['wheres'])) {
            $return['wheres'] = json_decode($post['wheres'], true);
            $this->getLogDriver()->addDebug(json_encode($return['wheres']));
        }

        if (! empty($post['whereIns'])) {
            $return['whereIns'] = json_decode($post['whereIns'], true);
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
     * Takes common requests and appends them to the query object. Any other
     * special requirements will be handled after
     *
     * @param  Ps2alerts\Api\QueryObjects\QueryObject $queryObject
     * @param  array                                  $post
     *
     * @return Ps2alerts\Api\QueryObjects\QueryObject
     */
    public function setupQueryObject($queryObject, $post)
    {
        if (! empty($post['wheres'])) {
            foreach ($post['wheres'] as $key => $value) {
                $queryObject->addWhere([
                    'col'   => $key,
                    'value' => $value
                ]);
            }
        }

        if (! empty($post['whereIns'])) {
            foreach ($post['whereIns'] as $key => $value) {
                // Escape strings manually, incase of player IDs etc
                foreach ($value as $i => $val) {
                    if (is_string($val)) {
                        $value[$i] = "'{$val}'";
                    }
                }

                $queryObject->addWhereIn([
                    'col'   => $key,
                    'value' => implode(',', $value) // use implode for WHERE IN (x,x)
                ]);
            }
        }

        if (! empty($post['orderBy'])) {
            $queryObject->setOrderBy(array_keys($post['orderBy'])[0]);
            $queryObject->setOrderByDirection(array_values($post['orderBy'])[0]);
        }

        if (! empty($post['limit'])) {
            $queryObject->setLimit($post['limit']);
        }

        if (! empty($this->getFlags())) {
            // If there are some funky things we have to do, set them.
            $queryObject->setFlags($this->getFlags());
        }

        // This should always be set
        $queryObject->addWhere([
            'col'   => 'Valid',
            'value' => '1'
        ]);

        return $queryObject;
    }
}
