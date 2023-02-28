<?php
/**
 * Create by Ricky Huang
 * E-mail: ricky_huang_hkg@ensignfreight.com
 * Description: RedisHandler
 * Date: 2023-02-23 15:24
 * Update: 2023-02-23 15:24
 */

namespace Rickytech\Library\Services\Cache\Redis;

use Closure;
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class RedisHandler
{
    private static ?\Hyperf\Redis\RedisProxy $redis = null;
    private static ?RedisHandler $instance = null;
    private static int $expire = 3600; //默认存储时间（秒）

    private function __construct()
    {
        try {
            self::$redis = ApplicationContext::getContainer()->get(RedisFactory::class)->get('default');
            self::$redis->select((int)env('REDIS_HOST'));
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface|\RedisException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function getInstance(): ?RedisHandler
    {
        if (is_null(self::$instance) || !self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function set(mixed $key, $value, int $expire = 3600): bool
    {
        try {
            if (!$value) {
                $expire = 120;
                $value = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            } else {
                if (is_int($key) || is_string($key)) {
                    $value = is_int($value) ? $value : json_encode(
                        $value,
                        JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
                    );
                    $expire = (int)$expire ? $expire : self::$expire;
                }
            }
            return self::$redis->set($key, $value) && self::$redis->expire($key, $expire);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function expire(string $key, $expire = 0): bool
    {
        try {
            $expire = (int)$expire ? $expire : self::$expire;
            if (self::$redis->expire($key, $expire)) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function get(string $key)
    {
        try {
            $value = self::$redis->get($key);
            return is_numeric($value) ? $value : json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function del(string $key)
    {
        try {
            return self::$redis->del($key);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function substr(string $key, $start, $end = 0)
    {
        try {
            $value = self::get($key);
            if ($value && is_string($value)) {
                if ($end) {
                    return mb_substr($value, $start, $end);
                }
                return mb_substr($value, $start);
            }
            return false;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function replace(string $key, $value, $expire = 0)
    {
        try {
            $value = self::$redis->getSet($key, $value);
            $expire = (int)$expire ? $expire : self::$expire;
            self::$redis->expire($key, $expire);
            return is_numeric($value) ? $value : json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function mset(array $arr): bool
    {
        try {
            if ($arr && is_array($arr)) {
                foreach ($arr as &$value) {
                    $value = is_int($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                if (self::$redis->mset($arr)) {
                    return true;
                }
                return false;
            }
            return false;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function mGet()
    {
        try {
            $keys = func_get_args();
            if ($keys) {
                $values = self::$redis->mget($keys);
                if ($values) {
                    foreach ($values as &$value) {
                        $value = is_numeric($value) ? $value : json_decode($value, true);
                    }
                    return $values;
                }
            }
            return false;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function expireTime(string $key)
    {
        try {
            return self::$redis->ttl($key);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function setnx($key, $value, $expire = 0)
    {
        try {
            $value = is_int($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
            $res = self::$redis->setnx($key, $value);
            if ($res) {
                $expire = (int)$expire ? $expire : self::$expire;
                self::$redis->expire($key, $expire);
            }
            return $res;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function valueLength(string $key): int
    {
        try {
            $value = self::get($key);
            $length = 0;
            if ($value) {
                if (is_array($value)) {
                    $length = count($value);
                } else {
                    $length = strlen($value);
                }
            }
            return $length;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function inc(string $key, $int = 0)
    {
        try {
            if ((int)$int) {
                return self::$redis->incrby($key, $int);
            }
            return self::$redis->incr($key);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function dec($key, $int = 0)
    {
        try {
            if ((int)$int) {
                return self::$redis->decrby($key, $int);
            }
            return self::$redis->decr($key);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hSet(string $table, string $column, mixed $value, $expire = 0)
    {
        try {
            $value = is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) : $value;
            $res = self::$redis->hset($table, $column, $value);
            if ((int)$expire) {
                self::$redis->expire($table, $expire);
            }
            return $res;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hGet(string $table, string $column)
    {
        try {
            $value = self::$redis->hget($table, $column);
            return is_numeric($value) ? $value : json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hDel($table, $columns)
    {
        try {
            $columns = func_get_args();
            $table = $columns[0];
            $count = count($columns);
            $num = 0;
            for ($i = 1; $i < $count; $i++) {
                $num += self::$redis->hdel($table, $columns[$i]);
            }
            return $num;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hExists($table, $column): bool
    {
        try {
            if ((int)self::$redis->hexists($table, $column)) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hGetAll($table)
    {
        try {
            return self::$redis->hgetall($table);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hinc($table, $column, $num = 1)
    {
        try {
            $value = self::hget($table, $column);
            if (is_numeric($value)) { //数字类型，包括整数和浮点数
                $value += $num;
                self::hset($table, $column, $value);
                return $value;
            }
            return false;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hKeys($table)
    {
        try {
            return self::$redis->hkeys($table);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hVals($table)
    {
        try {
            return self::$redis->hvals($table);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hLen($table)
    {
        try {
            return self::$redis->hlen($table);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hMGet($table, $columns): array
    {
        $data = self::hgetall($table);
        $result = [];
        if ($data) {
            $columns = func_get_args();
            unset($columns[0]);
            foreach ($columns as $value) {
                $result[$value] = $data[$value] ?? null;
            }
        }
        return $result;
    }

    public static function hMSet($table, array $data, $expire = 0)
    {
        try {
            $result = self::$redis->hmset($table, $data);
            if ((int)$expire) {
                self::expire($table, $expire);
            }
            return $result;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hSetNX($table, $column, $value, $expire = 0): bool
    {
        try {
            if (is_array($value)) {
                $value = json_encode($value, JSON_THROW_ON_ERROR);
            }
            $result = self::$redis->hSetNx($table, $column, $value);
            if ((int)$expire) {
                self::expire($table, $expire);
            }
            return $result;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function remember($key, Closure $callback, int $expire = 0, int $hash = 0)
    {
        try {
            $value = self::get($key);
            if (!is_bool($value)) {
                return is_numeric($value) ? $value : json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            }
            $value = $callback();
            if ($value) {
                $value = is_int($value) ? $value : json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                $time = random_int(100, 1000);
                $expire = $expire ? $expire + $time : self::$expire + $time;
            } else {
                $expire = 120;
            }
            $hash ? self::set($key, $value, $expire) : self::hSet($key, $value, $expire);
            return $callback();
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}
