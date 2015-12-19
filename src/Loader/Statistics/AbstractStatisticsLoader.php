<?php

namespace Ps2alerts\Api\Loader\Statistics;

use Ps2alerts\Api\Loader\AbstractLoader;
use Ps2alerts\Api\QueryObjects\QueryObject;

abstract class AbstractStatisticsLoader extends AbstractLoader
{
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
}
