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
        if ($value !== null) {
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
        return $this->redisClient->get($key);
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
    public function put($key, $value, int|string $ttl = 3600): bool
    {
        if ($ttl === '-1') {
            return $this->redisClient->set($key, $value);
        }
        return $this->redisClient->set($key, $value, $ttl);
    }

    /**
     * @param $key
     * @param $field
     * @param $value
     * @param bool $encode
     * @param bool $serialize
     * @return mixed
     */
    public function setHashData($key, $field, $value, bool $encode = false, bool $serialize = true)
    {
        if ($encode) {
            $value = json_encode($value);
        } elseif ($serialize) {
            $value = serialize($value);
        }
        return $this->redisClient->hSet($key, $field, $value);
    }

    /**
     * @param $key
     * @param $field
     * @param bool $decode
     * @param bool $unserialize
     * @return mixed
     */
    public function getHashDataField($key, $field, bool $decode = false, bool $unserialize = false)
    {
        if ($decode) {
            return json_decode($this->redisClient->hGet($key, $field));
        } elseif ($unserialize) {
            return unserialize($this->redisClient->hGet($key, $field));
        }
        return $this->redisClient->hGet($key, $field);
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

    public function serializeCache($key, $ttl, \Closure $callback)
    {
        $value = $this->get($key);
        if (!is_bool($value)) {
            return unserialize($value);
        }
        $this->put($key, $value = serialize($callback()), $ttl);
        return unserialize($value);
    }

    public function deleteHashDataFieldByKey(string $key, string $field)
    {
        return $this->redisClient->hDel($key, $field);
    }

    public function rememberHashData(
        string $key,
        string $field,
        \Closure $closure,
        $ttl = 7200
    ) {
        $value = $this->getHashDataField($key, $field, false, true);

        if (!is_bool($value)) {
            return $value;
        }
        !$value = $closure();
        if (!$value) {
            return $value;
        } else {
            $this->setHashDataByCallback($key, $field, $value, $ttl);
        }

        return $value;
    }

    private function setHashDataByCallback($key, $field, $data, $ttl)
    {
        $this->redisClient->hSet($key, $field, serialize($data));
        $this->redisClient->expire($key, $ttl);
    }
}
