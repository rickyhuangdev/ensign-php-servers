<?php

namespace Rickytech\Library\Services\Cache\Redis;

use Hyperf\Cache\Helper\StringHelper;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Str;
use Illuminate\Support\Arr;
use Rickytech\Library\DataTransferObject\DataTransferObject;


class CacheManager
{
    private ?RedisHandler $cache;

    public function __construct()
    {
        $this->cache = RedisHandler::getInstance();
    }

    public function set($key, $result, $ttl = 0): void
    {
        $this->cache::set($key, $result, $ttl);
    }


    public function get(string $key): array
    {
        $value = $this->cache::get($key);
        if ($value) {
            return [true, $value];
        }
        return [false, null];
    }

    public function hashGet(string $key, string $field): array
    {
        $value = $this->cache::hGet($key, $field);
        if ($value) {
            return [true, $value];
        }
        return [false, null];
    }


    public function hashSet(string $key, string $field, mixed $value)
    {
        return $this->cache::hSet($key, $field, $value);
    }


    public function format(string $prefix, mixed $arguments, ?string $value = null): string
    {
        $arguments = array_shift($arguments);
        if ($arguments instanceof DataTransferObject) {
            $arguments = $arguments->toArray();
        }
        if ($value !== null) {
            if ($matches = StringHelper::parse($value)) {
                foreach ($matches as $search) {
                    $k = str_replace(['#{', '}'], '', $search);
                    $value = Str::replaceFirst($search, (string)$this->data_get($arguments, $k), $value);
                }
            }
        } else {
            $value = implode(':', $arguments);
        }

        return $prefix . ':' . $value;
    }

    /**
     * Parse expression of value.
     */
    public function parse(string $value): array
    {
        preg_match_all('/\#\{[\w\.]+\}/', $value, $matches);

        return $matches[0] ?? [];
    }

    public function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }
        $key = is_array($key) ? $key : explode('.', is_int($key) ? (string)$key : $key);
        while (!is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!is_array($target)) {
                    return value($default);
                }
                $result = [];
                foreach ($target as $item) {
                    $result[] = data_get($item, $key);
                }
                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }
            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }
        return $target;
    }


}
