<?php
/**
 * Create by Ricky Huang
 * E-mail: ricky_huang_hkg@ensignfreight.com
 * Description: RedisHandler
 * Date: 2023-02-23 15:24
 * Update: 2023-02-23 15:24
 */

namespace Rickytech\Library\Services\Cache\Redis;

class RedisHandler
{
    private static $redis = null;
    private static int $expire = 3600; //默认存储时间（秒）

    public function __construct()
    {
        self::$redis = new \Predis\Client([
            'scheme'   => 'tcp',
            'host'     => env("REDIS_HOST"),
            'port'     => env("REDIS_PORT"),
            'database' => (int)env("REDIS_DB"),
            'password' => env('REDIS_AUTH'),
        ]);
    }

    public static function set(mixed $key, $value, int $expire = 3600): bool
    {
        if (!$value) {
            $expire = 120;
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            if (is_int($key) || is_string($key)) {
                $value = is_int($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
                $expire = (int)$expire ? $expire : self::$expire;
            }
        }
        return self::$redis->set($key, $value) && self::$redis->expire($key, $expire);
    }

    public static function expire(string $key, $expire = 0): bool
    {
        $expire = (int)$expire ? $expire : self::$expire;
        if (self::$redis->expire($key, $expire)) {
            return true;
        }
        return false;
    }

    public static function get(string $key)
    {
        $value = self::$redis->get($key);
        if (is_object($value)) {
            return $value;
        }
        return is_numeric($value) ? $value : json_decode($value, true);
    }

    public static function del(string $key)
    {
        return self::$redis->del($key);
    }

    public static function substr(string $key, $start, $end = 0)
    {
        $value = self::get($key);
        if ($value && is_string($value)) {
            if ($end) {
                return mb_substr($value, $start, $end);
            }
            return mb_substr($value, $start);
        }
        return false;
    }

    public static function replace(string $key, $value, $expire = 0)
    {
        $value = self::$redis->getSet($key, $value);
        $expire = (int)$expire ? $expire : self::$expire;
        self::$redis->expire($key, $expire);
        return is_numeric($value) ? $value : json_decode($value, true);
    }

    public static function mset(array $arr): bool
    {
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
    }

    public static function mget(): bool
    {
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
    }

    public static function expireTime(string $key)
    {
        return self::$redis->ttl($key);
    }

    public static function setnx($key, $value, $expire = 0)
    {
        $value = is_int($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
        $res = self::$redis->setnx($key, $value);
        if ($res) {
            $expire = (int)$expire ? $expire : self::$expire;
            self::$redis->expire($key, $expire);
        }
        return $res;
    }

    public static function valueLength(string $key): int
    {
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
    }

    public static function inc(string $key, $int = 0)
    {
        if ((int)$int) {
            return self::$redis->incrby($key, $int);
        }

        return self::$redis->incr($key);
    }

    public static function dec($key, $int = 0)
    {
        if ((int)$int) {
            return self::$redis->decrby($key, $int);
        }

        return self::$redis->decr($key);
    }

    public static function hSet(string $table, string $column, mixed $value, $expire = 0)
    {
        $value = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
        $res = self::$redis->hset($table, $column, $value);
        if ((int)$expire) {
            self::$redis->expire($table, $expire);
        }
        return $res;
    }

    public static function hGet(string $table, string $column)
    {
        return self::$redis->hget($table, $column);
    }

    public static function hDel($table, $columns)
    {
        $columns = func_get_args();
        $table = $columns[0];
        $count = count($columns);
        $num = 0;
        for ($i = 1; $i < $count; $i++) {
            $num += self::$redis->hdel($table, $columns[$i]);
        }
        return $num;
    }

    public static function hExists($table, $column): bool
    {
        if ((int)self::$redis->hexists($table, $column)) {
            return true;
        }
        return false;
    }

    public static function hGetAll($table)
    {
        return self::$redis->hgetall($table);
    }

    public static function hinc($table, $column, $num = 1)
    {
        $value = self::hget($table, $column);
        if (is_numeric($value)) { //数字类型，包括整数和浮点数
            $value += $num;
            self::hset($table, $column, $value);
            return $value;
        }

        return false;
    }

    public static function hKeys($table)
    {
        return self::$redis->hkeys($table);
    }

    public static function hVals($table)
    {
        return self::$redis->hvals($table);
    }

    public static function hLen($table)
    {
        return self::$redis->hlen($table);
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
        $result = self::$redis->hmset($table, $data);
        if ((int)$expire) {
            self::expire($table, $expire);
        }
        return $result;
    }

    public static function hSetnx($table, $column, $value, $expire = 0)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $result = self::$redis->hsetnx($table, $column, $value);
        if ((int)$expire) {
            self::expire($table, $expire);
        }
        return $result;
    }


}
