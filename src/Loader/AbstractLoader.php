<?php

namespace Ps2alerts\Api\Loader;

use Ps2Alerts\Api\Contract\RedisAwareInterface;
use Ps2Alerts\Api\Contract\RedisAwareTrait;

abstract class AbstractLoader implements RedisAwareInterface
{
    use RedisAwareTrait;

    public function checkRedis($key)
    {
        return $this->getRedisDriver()->exists($key);
    }

    public function getFromRedis($key)
    {
        return json_decode($this->getRedisDriver()->get($key));
    }

    public function setExpireKey($key, $value, $secs = 3600)
    {
        $value = json_encode($value);
        return $this->getRedisDriver()->setEx($key, $secs, $value);
    }

    public function cacheAndReturn($result, $key)
    {
        $this->setExpireKey($key, $result);
        $result['cached'] = 0;
        return $result;
    }
}
