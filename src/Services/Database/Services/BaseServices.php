<?php
declare(strict_types=1);

namespace Rickytech\Library\Services\Database\Services;

use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class BaseServices
{
    /**
     * @var Object $dao
     */
    protected object $dao;

    protected ?Redis $cache = null;

    public function __construct()
    {
        try {
            $this->cache = ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new \RuntimeException($e->getMessage(), 500);
        }
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
