<?php

namespace Rickytech\Library\Services\Cache\Instances;

use Exception;
use Hyperf\Utils\ApplicationContext;
use Rickytech\Library\Services\Cache\Contracts\Cache;

final class PhpRedis implements Cache
{
    private static ?PhpRedis $phpRedis = null;
    protected mixed $redisClient;
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

    public function remember($key, $ttl, \Closure $callback)
    {
        $value = $this->get($key);
        if ($value !== false) {
            return $value;
        }
        $this->put($key, $value = $callback(), value($ttl));
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

    public function get($key, $default = null)
    {
        return unserialize($this->redisClient->get($key));
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
     * @param $value
     * @param int $ttl
     * @return bool
     */
    public function put($key, $value, int $ttl = 3600): bool
    {
        return $this->redisClient->set($key, serialize($value), $ttl);
    }
}
