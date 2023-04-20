<?php
/**
 * Create by Ricky Huang
 * E-mail: ricky_huang_hkg@ensignfreight.com
 * Description: RedisHandler
 * Date: 2023-02-23 15:24
 * Update: 2023-02-23 15:24
 */

namespace Rickytech\Library\Services\Cache\Redis;

use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\RedisFactory;

class RedisClient
{
    private ?\Hyperf\Redis\RedisProxy $redis = null;
    public function __construct()
    {
        try {
            $redis = ApplicationContext::getContainer()->get(RedisFactory::class)->get('default');
            $this->redis = $redis;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), 500);
        }
    }

    public function set(mixed $key, $value, int $expire = 3600): bool
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
                    $expire = (int)$expire ? $expire : $this->$expire;
                }
            }
            $this->redis->select((int)env('REDIS_DB'));
            return $this->redis->set($key, $value) && $this->redis->expire($key, $expire);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function expire(string $key, $expire = 0): bool
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            $expire = (int)$expire ? $expire : $this->$expire;
            if ($this->redis->expire($key, $expire)) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function get(string $key)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            $value = $this->redis->get($key);
            if ($value) {
                return is_numeric($value) ? $value : json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            }
            return $value;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function del(string $key)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            return $this->redis->del($key);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function substr(string $key, $start, $end = 0)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            $value = $this->get($key);
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

    public function replace(string $key, $value, $expire = 0)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            $value = $this->redis->getSet($key, $value);
            $expire = (int)$expire ? $expire : $this->$expire;
            $this->redis->expire($key, $expire);
            return is_numeric($value) ? $value : json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function mset(array $arr): bool
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            if ($arr && is_array($arr)) {
                foreach ($arr as &$value) {
                    $value = is_int($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                if ($this->redis->mset($arr)) {
                    return true;
                }
                return false;
            }
            return false;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function mGet()
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            $keys = func_get_args();
            if ($keys) {
                $values = $this->redis->mget($keys);
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

    public function expireTime(string $key)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            return $this->redis->ttl($key);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function setnx($key, $value, $expire = 0)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            $value = is_int($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
            $res = $this->redis->setnx($key, $value);
            if ($res) {
                $expire = (int)$expire ? $expire : $this->$expire;
                $this->redis->expire($key, $expire);
            }
            return $res;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function valueLength(string $key): int
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            $value = $this->get($key);
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

    public function inc(string $key, $int = 0)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            if ((int)$int) {
                return $this->redis->incrby($key, $int);
            }
            return $this->redis->incr($key);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function dec($key, $int = 0)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            if ((int)$int) {
                return $this->redis->decrby($key, $int);
            }
            return $this->redis->decr($key);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function hSet(string $table, string $column, mixed $value, $expire = 0)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            $value = is_array($value) ? json_encode($value) : $value;
            $res = $this->redis->hset($table, $column, $value);
            if ((int)$expire) {
                $this->redis->expire($table, $expire);
            }
            if (is_null($value)) {
                $this->redis->expire($table, 120);
            }
            return $res;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function hGet(string $table, string $column)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            return $this->redis->hget($table, $column);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function hDel($table, $columns)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            $columns = func_get_args();
            $table = $columns[0];
            $count = count($columns);
            $num = 0;
            for ($i = 1; $i < $count; $i++) {
                $num += $this->redis->hdel($table, $columns[$i]);
            }
            return $num;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function hExists($table, $column): bool
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            if ((int)$this->redis->hexists($table, $column)) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function hGetAll($table)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            return $this->redis->hgetall($table);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function hinc($table, $column, $num = 1)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            $value = $this->hget($table, $column);
            if (is_numeric($value)) { //数字类型，包括整数和浮点数
                $value += $num;
                $this->hset($table, $column, $value);
                return $value;
            }
            return false;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function hKeys($table)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            return $this->redis->hkeys($table);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function hVals($table)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            return $this->redis->hvals($table);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function hLen($table)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            return $this->redis->hlen($table);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function hMGet($table, $columns): array
    {
        $data = $this->hgetall($table);
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

    public function hMSet($table, array $data, $expire = 0)
    {
        try {
            $this->redis->select((int)env('REDIS_DB'));
            $result = $this->redis->hmset($table, $data);
            if ((int)$expire) {
                $this->expire($table, $expire);
            }
            return $result;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function hSetNX($table, $column, $value, $expire = 0): bool
    {
        try {
            if (is_array($value)) {
                $value = json_encode($value, JSON_THROW_ON_ERROR);
            }
            $this->redis->select((int)env('REDIS_DB'));
            $result = $this->redis->hSetNx($table, $column, $value);
            if ((int)$expire) {
                $this->expire($table, $expire);
            }
            return $result;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function remember(string $key, callable $callback, int $ttl = 60)
    {

        try {
            $this->redis->select((int)env('REDIS_DB'));
            $value = $this->redis->get($key);
            if ($value === false) {
                $lock = $this->redis->set("lock:$key", 1, ['NX', 'EX' => 10]);
                if ($lock) {
                    $value = $callback();
                    $this->redis->set($key, json_encode($value, JSON_THROW_ON_ERROR), $ttl + random_int(0, 10));
                    $this->redis->del("lock:$key");
                } else {
                    usleep(100);
                    return $this->remember($key, $callback, $ttl);
                }
            }
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function rememberHash(string $key, string $field, callable $callback, int $ttl = 60)
    {

        try {
            $this->redis->select((int)env('REDIS_DB'));
            $value = $this->redis->hGet($key, $field);
            if ($value === false) {
                $lock = $this->redis->set("lock:$key:$field", 1, ['NX', 'EX' => 10]);

                if ($lock) {
                    $value = $callback();
                    $this->redis->hSet($key, $field, json_encode($value, JSON_THROW_ON_ERROR));
                    $this->redis->expire($key, $ttl + random_int(0, 10));
                    // 释放锁
                    $this->redis->del("lock:$key:$field");
                } else {
                    // 等待锁释放，然后重新尝试获取缓存
                    usleep(100);
                    return $this->rememberHash($key, $field, $callback, $ttl);
                }
            }
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}
