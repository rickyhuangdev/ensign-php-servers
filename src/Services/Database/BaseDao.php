<?php
/**
 * Create by Ricky Huang
 * E-mail: ricky_huang_hkg@ensignfreight.com
 * Description: BaseDao
 * Date: 2023-03-10 12:05
 * Update: 2023-03-10 12:05
 */
declare(strict_types=1);

namespace Rickytech\Library\Services\Database;

use Hyperf\Context\Context;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Utils\Collection;
use Rickytech\Library\Filter\QueryFilter;

abstract class BaseDao
{
    /**
     * 获取当前模型类名.
     *
     * @return string
     */
    abstract protected function model(): string;

    /**
     * 获取模型实例.
     *
     * @return Model
     */
    protected function getModel(): Model
    {
        return Context::getOrSet(__METHOD__, function () {
            $model = $this->model();
            return new $model;
        });
    }

    /**
     * 根据ID获取模型实例.
     *
     * @param $id
     * @return Model|null
     */
    public function findById($id): ?Model
    {
        return $this->getModel()->newQuery()->find($id);
    }

    /**
     * 根据条件获取单个模型实例.
     *
     * @param array $where
     * @param array $columns
     * @return Model|null
     */
    public function findOneBy(array $where, array $columns = ['*']): ?Model
    {
        return $this->getModel()->newQuery()->where($where)->first($columns);
    }

    /**
     * 根据条件获取多个模型实例.
     *
     * @param array $where
     * @param array $columns
     * @param array $orders
     * @param int $offset
     * @param int $limit
     * @return Collection
     */
    public function findBy(array $where, array $columns = ['*'], array $orders = [], int $offset = 0, int $limit = 0): Collection
    {
        $query = $this->getModel()->newQuery()->where($where)->select($columns);
        foreach ($orders as $order) {
            $query->orderBy($order[0], $order[1] ?? 'asc');
        }
        if ($limit > 0) {
            $query->offset($offset)->limit($limit);
        }
        return $query->get();
    }

    /**
     * 根据条件获取分页数据.
     *
     * @param array $where
     * @param array $columns
     * @param array $orders
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function findByPage(array $where, array $columns = ['*'], array $orders = [], int $current = 1, int $pageSize = 20): array
    {
        $total = $this->getModel()->newQuery()->where($where)->count();
        $offset = ($current - 1) * $pageSize;
        $items = $this->findBy($where, $columns, $orders, $offset, $pageSize);
        return compact('total', 'items', 'pageSize', 'current');
    }

    /**
     * 根据条件获取分页数据.
     *
     * @param array $where
     * @param array $columns
     * @param array $orders
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function findByFilter(array $where, array $columns = ['*'], array $orders = [], int $current = 1, int $pageSize = 20, QueryFilter $filter = null): array
    {
        $items = $this->getModel()->newQuery()->when($filter !== null, function ($query) use ($filter) {
            $query->filter($filter);
        })->where($where)->select($columns)->when($orders, function ($query) use ($orders) {
            foreach ($orders as $order) {
                $query->orderBy($order[0], $order[1] ?? 'asc');
            }
        })->get();
        $total = $items->count();
        return compact('total', 'items', 'pageSize', 'current');
    }


    /**
     * 创建模型实例.
     *
     * @param array $attributes
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model
     */
    public function create(array $attributes): \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model
    {
        return $this->getModel()->newQuery()->create($attributes);
    }

    /**
     * 批量创建模型实例.
     *
     * @param array $data
     * @return bool
     */
    public function createBatch(array $data): bool
    {
        return Db::table($this->getModel()->getTable())->insert($data);
    }

    /**
     * 更新模型实例.
     *
     * @param Model $model
     * @param array $attributes
     * @return bool
     */
    public function update(Model $model, array $attributes): bool
    {
        return $model->fill($attributes)->save();
    }

    /**
     * 删除模型实例.
     *
     * @param Model $model
     * @return bool|null
     */
    public function delete(Model $model): ?bool
    {
        return $model->delete();
    }

    /**
     * 开始数据库事务.
     *
     * @return void
     */
    public function beginTransaction(): void
    {
        Db::beginTransaction();
    }

    /**
     * 提交数据库事务.
     *
     * @return void
     */
    public function commit(): void
    {
        Db::commit();
    }

    /**
     * 回滚数据库事务.
     *
     * @return void
     */
    public function rollBack(): void
    {
        Db::rollBack();
    }

    /**
     * 创建一对多关联.
     *
     * @param string $relatedModel
     * @param string $foreignKey
     * @param string $localKey
     * @return HasMany
     */
    protected function hasMany(string $relatedModel, string $foreignKey, string $localKey = 'id'): HasMany
    {
        return $this->getModel()->hasMany($relatedModel, $foreignKey, $localKey);
    }

    /**
     * 创建一对一关联.
     *
     * @param string $relatedModel
     * @param string $foreignKey
     * @param string $localKey
     * @return HasOne
     */
    protected function hasOne(string $relatedModel, string $foreignKey, string $localKey = 'id'): HasOne
    {
        return $this->getModel()->hasOne($relatedModel, $foreignKey, $localKey);
    }

    /**
     * 创建属于关联.
     *
     * @param string $relatedModel
     * @param string $foreignKey
     * @param string $ownerKey
     * @return BelongsTo
     */
    protected function belongsTo(string $relatedModel, string $foreignKey, string $ownerKey = 'id'): BelongsTo
    {
        return $this->getModel()->belongsTo($relatedModel, $foreignKey, $ownerKey);
    }

    /**
     * @return array
     */
    protected function getColumns(): array
    {
        return Schema::getColumnListing($this->getModel()->getTable());
    }
}
