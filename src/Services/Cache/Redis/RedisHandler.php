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
use Hyperf\Context\ApplicationContext;

class RedisHandler
{
    private static ?\Hyperf\Redis\RedisProxy $redis = null;
    private static ?RedisHandler $instance = null;
    private static int $expire = 3600; //默认存储时间（秒）

    private function __construct()
    {
        try {
            $redis = ApplicationContext::getContainer()->get(RedisFactory::class)->get('default');
            self::$redis = $redis;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), 500);
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
            self::$redis->select((int)env('REDIS_DB'));
            return self::$redis->set($key, $value) && self::$redis->expire($key, $expire);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function expire(string $key, $expire = 0): bool
    {
        try {
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
            $value = self::$redis->get($key);
            if ($value) {
                return is_numeric($value) ? $value : json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            }
            return $value;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function del(string $key)
    {
        try {
            self::$redis->select((int)env('REDIS_DB'));
            return self::$redis->del($key);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function substr(string $key, $start, $end = 0)
    {
        try {
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
            return self::$redis->ttl($key);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function setnx($key, $value, $expire = 0)
    {
        try {
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
            $value = is_array($value) ? json_encode($value) : $value;
            $res = self::$redis->hset($table, $column, $value);
            if ((int)$expire) {
                self::$redis->expire($table, $expire);
            }
            if (is_null($value)) {
                self::$redis->expire($table, 120);
            }
            return $res;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hGet(string $table, string $column)
    {
        try {
            self::$redis->select((int)env('REDIS_DB'));
            return self::$redis->hget($table, $column);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hDel($table, $columns)
    {
        try {
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
            return self::$redis->hgetall($table);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hinc($table, $column, $num = 1)
    {
        try {
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
            return self::$redis->hkeys($table);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hVals($table)
    {
        try {
            self::$redis->select((int)env('REDIS_DB'));
            return self::$redis->hvals($table);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function hLen($table)
    {
        try {
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
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
            self::$redis->select((int)env('REDIS_DB'));
            $result = self::$redis->hSetNx($table, $column, $value);
            if ((int)$expire) {
                self::expire($table, $expire);
            }
            return $result;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public  static function remember(string $key, callable $callback, int $expire = 3600, int $nullExpire = 300)
    {
        // 检查 key 是否存在于 Redis 中
        if (self::$redis->exists($key)) {
            // 如果 key 存在，直接从 Redis 获取数据并返回
            return self::$redis->get($key);
        } else {
            $data = $callback();
            if ($data === null) {
                $expire = $nullExpire;
            } else {
                // 将数据存储到 Redis 中
                self::$redis->set($key, $data);
            }

            // 设置过期时间
            self::$redis->expire($key, $expire);

            // 返回获取到的数据
            return $data;
        }
    }

    public function rememberHash(string $key, callable $callback, int $expire = 3600, int $nullExpire = 300)
    {
        // 检查 key 是否存在于 Redis 中
        if (self::$redis->exists($key)) {
            // 如果 key 存在，直接从 Redis 获取数据并返回
            return self::$redis->hGetAll($key);
        } else {
            // 如果 key 不存在，执行回调函数以获取数据
            $data = $callback();

            // 检查数据是否为 null
            if ($data === null) {
                // 如果数据为 null，设置较短的缓存时间
                $expire = $nullExpire;
            } else {
                // 将数据存储到 Redis 的哈希表中
                self::$redis->hMSet($key, $data);
            }

            // 设置过期时间
            self::$redis->expire($key, $expire);

            // 返回获取到的数据
            return $data;
        }
    }
}
