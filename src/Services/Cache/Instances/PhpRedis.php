<?php

namespace Rickytech\Library\Services\Cache\Instances;

use Exception;
use Hyperf\Utils\ApplicationContext;
use Rickytech\Library\Services\Cache\Contracts\Cache;

/**
 *
 */
final class PhpRedis implements Cache
{
    /**
     * @var PhpRedis|null
     */
    private static ?PhpRedis $phpRedis = null;
    /**
     * @var mixed
     */
    public mixed $redisClient;
    /**
     * @var int
     */
    protected int $default = 3600;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function __construct()
    {
        $this->redisClient = ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
    }

    /**
     * @return void
     */
    private function __clone(): void
    {
    }

    /**
     * @throws Exception
     */
    public function __wakeup(): never
    {
        throw new Exception("Cannot unserialize phpredis client");
    }

    /**
     * @return PhpRedis|null
     */
    public static function getPhpRedis(): ?PhpRedis
    {
        if (self::$phpRedis === null) {
            self::$phpRedis = new self();
        }
        return self::$phpRedis;
    }

    /**
     * @param $key
     * @param $ttl
     * @param \Closure $callback
     * @return mixed
     */
    public function remember($key, $ttl, \Closure $callback)
    {
        $value = $this->get($key);
        if ($value !== false) {
            return $value;
        }
        $this->put($key, $value = $callback(), $ttl);
        return $value;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key): bool
    {
        return !is_null($this->redisClient->get($key));
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return json_decode($this->redisClient->get($key),true);
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key): bool
    {
        return (bool)$this->redisClient->del($key);
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        return $this->redisClient->flushAll();
    }

    /**
     * @param $key
     * @return bool
     */
    public function checkKeyExist($key): bool
    {
        return (bool)$this->redisClient->exists($key);
    }

    /**
     * @param $key
     * @param $value
     * @param int $ttl
     * @return bool
     */
    public function put($key, $value, int $ttl = 3600): bool
    {
        return $this->redisClient->set($key, json_encode($value, JSON_UNESCAPED_UNICODE), $ttl);
    }

    /**
     * @param $key
     * @param $field
     * @param $value
     * @return string
     */
    public function setHashData($key, $field, $value)
    {
        return $this->redisClient->hSet($key, $field, json_encode($value, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param $key
     * @param $field
     * @return mixed
     */
    public function getHashDataField($key, $field)
    {
        return json_decode($this->redisClient->hGet($key, $field),true);
    }

    /**
     * @param $key
     * @return bool
     */
    public function deleteHashDataByKey($key): bool
    {
        return $this->delete($key);
    }

    /**
     * @param $key
     * @param $field
     * @return bool
     */
    public function checkHashDataFieldExist($key, $field): bool
    {
        return $this->redisClient->hExists($key, $field);
    }
}
