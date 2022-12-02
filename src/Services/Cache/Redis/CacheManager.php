<?php

namespace Rickytech\Library\Services\Cache\Redis;

use Closure;
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use Rickytech\Library\Services\Cache\Redis\Attributes\Cacheable;

class CacheManager
{
    private \Hyperf\Redis\RedisProxy $cache;


    public function __construct()
    {
        try {
            $this->cache = ApplicationContext::getContainer()->get(RedisFactory::class)->get('default');
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * @throws \RedisException
     * @throws \JsonException
     * @throws \Exception
     */
    public function remember($key, Closure $callback, int $ttl = 7200)
    {
        $value = $this->cache->get($key);
        if (!is_bool($value)) {
            return is_numeric($value) ? $value : json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }
        $value = $callback();
        if ($value) {
            $value = is_int($value) ? $value : json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            $ttl += random_int(100, 1000);
        } else {
            $ttl = 120;
        }
        $this->cache->set($key, $value, $ttl);
        return $callback();
    }

    /**
     * @throws \RedisException
     * @throws \Exception
     */
    public function set($key, $result, $ttl = 7200)
    {
        if ($result) {
            $ttl += random_int(100, 1000);
            $result = is_int($result) ? $result : json_encode($result, JSON_THROW_ON_ERROR);
        } elseif (is_null($result)) {
            $ttl = 120;
            $result = serialize($result);
        }

        $this->cache->set($key, $result, $ttl);
    }


    public function get(string $key)
    {
        $this->cache->select((int)env('REDIS_DB', 1));
        $value = $this->cache->get($key);
        if ($value) {
            return [true, is_numeric($value) ? $value : json_decode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK, 512, JSON_THROW_ON_ERROR)];
        }
        return [false, null];
    }

    public function delete(string $key)
    {
        $this->cache->select((int)env('REDIS_DB', 1));
        $this->cache->del($key);
    }

    /**
     * @throws \ReflectionException
     */
    public function getCacheAttributeValues(string $className, string $methodName): array
    {
        try {
            $class = new ReflectionClass($className);
            $attributes = $class->getMethod($methodName)->getAttributes(Cacheable::class);
            $attributesArray = [];
            foreach ($attributes as $attribute) {
                try {
                    $attributeClass = $attribute->newInstance();
                } catch (\Exception $e) {
                    continue;
                }
                if (!$attributeClass instanceof Cacheable) {
                    continue;
                }
                $attributesArray['prefix'] = $attributeClass->prefix;
                $attributesArray['value'] = $attributeClass->value;
                $attributesArray['ttl'] = $attributeClass->ttl;
            }
            return array_values($attributesArray);
        } catch (\ReflectionException $e) {
            throw new \ReflectionException($e->getMessage());
        }
    }
}
