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

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;

abstract class BaseDao
{
    /**
     * @var string 模型类名称
     */
    protected $model;
    /**
     * @var array 可批量赋值的字段
     */
    protected $fillable = [];

    /**
     * BaseDao constructor.
     */
    public function __construct()
    {
        if (empty($this->model)) {
            throw new \RuntimeException('Please specify the $model property in ' . static::class);
        }
    }

    /**
     * 获取模型实例
     * @return Model
     */
    protected function model(): Model
    {
        return make($this->model);
    }

    /**
     * 根据主键获取模型实例
     * @param int $id 主键ID
     * @return Model|mixed|null
     */
    public function find(int $id)
    {
        return $this->model()->find($id);
    }

    /**
     * 根据条件获取模型实例
     * @param array $where 查询条件
     * @return Model|mixed|null
     */
    public function findWhere(array $where)
    {
        return $this->model()->where($where)->first();
    }

    /**
     * 根据条件获取多个模型实例
     * @param array $where 查询条件
     * @param array $orderBy 排序条件
     * @param array $columns 查询字段
     * @return Collection
     */
    public function findAll(array $where = [], array $orderBy = [], array $columns = ['*']): Collection
    {
        $query = $this->newQuery();
        $query = $this->applyConditions($query, $where);
        $query = $this->applyOrderBy($query, $orderBy);
        return $query->get($columns);
    }

    /**
     * 根据条件获取多个模型实例并进行分页
     * @param array $where 查询条件
     * @param array $orderBy 排序条件
     * @param int $perPage 每页数量
     * @param array $columns 查询字段
     * @param string $pageName 页码参数名称
     * @param int|null $page 当前页码
     * @return array
     */
    public function paginate(array $where = [], array $orderBy = [], int $perPage = 15, array $columns = ['*'], string $pageName = 'page', int $page = null): array
    {
        $query = $this->newQuery();
        $query = $this->applyConditions($query, $where);
        $query = $this->applyOrderBy($query, $orderBy);
        return $query->paginate($perPage, $columns, $pageName, $page)->toArray();
    }

    /**
     * 创建模型实例
     * @param array $attributes 属性
     * @return Model|mixed
     * @throws \Throwable
     */
    public function create(array $attributes)
    {
        $data = Arr::only($attributes, $this->fillable);
        $model = $this->model()->newInstance($data);
        $model->saveOrFail();
        return $model;
    }

    /**
     * 更新模型实例
     * @param Model $model 模型实例
     * @param array $attributes 属性
     * @return bool
     * @throws \Throwable
     */
    public function update(Model $model, array $attributes): bool
    {
        $data = Arr::only($attributes, $this->fillable);
        $model->fill($data);
        return $model->saveOrFail();
    }

    /**
     * 删除模型实例
     * @param Model $model 模型实例
     * @return bool|null
     * @throws \Exception
     */
    public function delete(Model $model): ?bool
    {
        return $model->delete();
    }

    /**
     * 新建查询构造器实例
     * @return Builder
     */
    protected function newQuery(): Builder
    {
        return $this->model()->newQuery();
    }

    /**
     * 应用查询条件
     * @param Builder $query 查询构造器
     * @param array $where 查询条件
     * @return Builder
     */
    protected function applyConditions(Builder $query, array $where): Builder
    {
        foreach ($where as $field => $value) {
            if (Str::contains($field, '.')) {
                [$relation, $field] = explode('.', $field);
                $query->whereHas($relation, function (Builder $query) use ($field, $value) {
                    $query->where($field, $value);
                });
            } else {
                $query->where($field, $value);
            }
        }
        return $query;
    }

    /**
     * 应用排序条件
     * @param Builder $query 查询构造器
     * @param array $orderBy 排序条件
     * @return Builder
     */
    protected function applyOrderBy(Builder $query, array $orderBy): Builder
    {
        foreach ($orderBy as $field => $direction) {
            $query->orderBy($field, $direction);
        }
        return $query;
    }

    /**
     * 开启事务
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return Db::beginTransaction();
    }

    /**
     * 提交事务
     * @return bool
     */
    public function commit(): bool
    {
        return Db::commit();
    }

    /**
     * 回滚事务
     * @return bool
     */
    public function rollBack(): bool
    {
        return Db::rollBack();
    }
}
