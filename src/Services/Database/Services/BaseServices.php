<?php
declare(strict_types=1);

namespace Rickytech\Library\Services\Database\Services;

use Hyperf\DbConnection\Db;

abstract class BaseServices
{
    /**
     * @var Object $dao
     */
    protected object $dao;

    public function transaction(callable $closure, bool $isTran = true)
    {
        return $isTran ? Db::transaction($closure) : $closure();
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->dao, $name], $arguments);
    }
}