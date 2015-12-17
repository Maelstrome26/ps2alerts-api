<?php

namespace Ps2alerts\Api\Loader;

use Ps2Alerts\Api\Contract\RedisAwareInterface;
use Ps2Alerts\Api\Contract\RedisAwareTrait;

abstract class AbstractLoader implements RedisAwareInterface
{
    use RedisAwareTrait;

    protected $cacheLoaderNamespace;

    public function setLoaderCacheNamespace($string)
    {
        $this->cacheLoaderNamespace = $string;
    }

    public function getLoaderCacheNamespace($string)
    {
        return $this->cacheLoaderNamespace;
    }

    /**
     * Checks for a key within Redis and returns it's existance
     *
     * @param  string  $key
     * @return boolean
     */
    public function checkRedis($key)
    {
        return $this->getRedisDriver()->exists("{$this->cacheLoaderNamespace}:{$key}");
    }

    /**
     * Retrieves a key from within Redis
     *
     * @param  string $key
     * @return string JSON decoded value
     */
    public function getFromRedis($key)
    {
        return json_decode($this->getRedisDriver()->get("{$this->cacheLoaderNamespace}:{$key}"));
    }

    /**
     * Sets JSON encoded value within Redis with an expiry in seconds
     *
     * @param string  $key
     * @param mixed   $value   Data to encode into JSON and store
     * @param integer $expires Cache expiry time in seconds
     */
    public function setExpireKey($key, $value, $expires = 3600)
    {
        $value = json_encode($value);
        return $this->getRedisDriver()->setEx("{$this->cacheLoaderNamespace}:{$key}", $expires, $value);
    }

    /**
     * Caches the data gathered and then returns again to the source script
     *
     * @param  mixed  $data Data to store
     * @param  string $key  Redis key to be used to store the data
     *
     * @return mixed  $data
     */
    public function cacheAndReturn($data, $key)
    {
        $this->setExpireKey("{$this->cacheLoaderNamespace}:{$key}", $data);
        $data['cached'] = 0;
        return $data;
    }
}
