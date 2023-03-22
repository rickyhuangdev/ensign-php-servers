<?php

declare(strict_types=1);

namespace Rickytech\Library\Services\Database\Services;

use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Rickytech\Library\Services\Cache\Redis\RedisHandler;

abstract class BaseServices
{
    /**
     * @var Object $dao
     */
    protected object $dao;

    protected RedisHandler $cache;

    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function __construct()
    {
        $this->cache = RedisHandler::getInstance();
    }

    public function transaction(callable $closure, bool $isTran = true)
    {
        return $isTran ? Db::transaction($closure) : $closure();
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->dao, $name], $arguments);
    }

    public function validator(array $data, array $rules): void
    {
        $validator = $this->validationFactory->make(
            $data,
            $rules
        );
        if ($validator->fails()) {
            throw new \RuntimeException($validator->errors()->first(), 422);
        }
    }

    public function pageResult(array $items, int $current, int $pageSize): array
    {
        $total = count($items);
        return compact('total', 'items', 'pageSize', 'current');
    }
}
