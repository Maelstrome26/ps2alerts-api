<?php

namespace Ps2alerts\Api\Contract;

use Predis\Client as Redis;

interface RedisAwareInterface
{
    /**
     * Set the Redis driver
     *
     * @param \Predis\Client
     */
    public function setRedisDriver(Redis $redis);

    /**
     * Get the Redis driver
     *
     * @return \Predis\Client
     */
    public function getRedisDriver();
}
