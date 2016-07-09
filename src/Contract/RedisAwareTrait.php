<?php

namespace Ps2alerts\Api\Contract;

use Predis\Client as Redis;

trait RedisAwareTrait
{
    /**
     * @var \Predis\Client
     */
    protected $redis;

    /**
     * @var \Predis\Client
     */
    protected $redisCache;

    /**
     * Set the Redis driver
     *
     * @param \Predis\Client $redis
     */
    public function setRedisDriver(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Get the Redis driver
     *
     * @return \Predis\Client
     */
    public function getRedisDriver()
    {
        return $this->redis;
    }

    /**
     * Set the Redis Cache driver
     *
     * @param \Predis\Client
     */
    public function setRedisCacheDriver(Redis $redis)
    {
        $this->redisCache = $redis;
    }

    /**
     * Get the Redis Cache driver
     *
     * @return \Predis\Client
     */
    public function getRedisCacheDriver()
    {
        return $this->redisCache;
    }
}
