<?php

declare(strict_types=1);

namespace Rickytech\Library\Services\Database\Services;

use Hyperf\DbConnection\Db;
use Rickytech\Library\Services\Cache\Redis\RedisHandler;

abstract class BaseServices
{
    /**
     * @var Object $dao
     */
    protected object $dao;

    protected RedisHandler $cache;

    public function __construct()
    {
        $this->cache = make(RedisHandler::class);
    }

    public function transaction(callable $closure, bool $isTran = true)
    {
        return $isTran ? Db::transaction($closure) : $closure();
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->dao, $name], $arguments);
    }
}
