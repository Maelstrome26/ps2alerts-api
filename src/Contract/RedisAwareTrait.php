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
     * Sets the Redis driver
     *
     * @param \Predis\Client $redis
     */
    public function setRedisDriver(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Gets the Redis driver
     *
     * @return \Predis\Client
     */
    public function getRedisDriver()
    {
        return $this->redis;
    }
}
