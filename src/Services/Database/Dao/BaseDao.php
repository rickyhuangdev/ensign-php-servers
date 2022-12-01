<?php

namespace Rickytech\Library\Services\Database\Dao;

use Carbon\Carbon;
use Hyperf\Contract\IdGeneratorInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Rickytech\Library\Filter\QueryFilter;
use Rickytech\Library\Services\Models\BaseMapperInterface;
use Rickytech\Library\Traits\TreeList;

abstract class BaseDao implements BaseMapperInterface
{
    use TreeList;

    abstract protected function setModel(): string;

    public function getModel(): Model|null
    {
        try {
            return ApplicationContext::getContainer()->get($this->getModel());
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    public function list(): Collection|null
    {
        return $this->getModel()::all();
    }

    public function listByIds(array $ids, array $field = ['*']): Collection|null
    {
        return $this->getModel()::select($field)->whereIn('id', $ids)->get();
    }

    public function getById(string $id, array $fields = ['*'], array $relations = []): Model|null
    {
        return $this->getModel()::where('id', $id)
            ->when($relations, function ($query) use ($relations) {
                return $query->with($relations);
            })
            ->select($fields)
            ->first();
    }

    public function getByIds(array $ids, array $fields = ['*'], array $relations = []): Model|null
    {
        return $this->getModel()::query()
            ->when($relations, function ($query) use ($relations) {
                return $query->with($relations);
            })
            ->select($fields)
            ->find($ids);
    }

    public function getOne(array $where, $fields = ['*'], array $relations = []): Model|null
    {
        return $this->getModel()::query()
            ->when($relations, function ($query) use ($relations) {
                return $query->with($relations);
            })
            ->where($where)
            ->select($fields)
            ->first();
    }

    public function getOneOrFail(string $id, $fields = ['*']): Model|null
    {
        return $this->getModel()::query()->select($fields)->findOrFail($id);
    }

    public function save(array $data): Model
    {
        return $this->getModel()::create($data);
    }


    public function saveBatch(array $data, $primaryKey = 'id', $autoIncrement = false, $autoInsertDate = true): bool
    {
        if (!$autoIncrement) {
            foreach ($data as &$value) {
                if (Arr::exists($value, $primaryKey)) {
                    $value['id'] = $this->getPrimaryKeyValue();
                }
                if ($autoInsertDate) {
                    $value['created_at'] = Carbon::now('prc')->toDateTimeString();
                    $value['updated_at'] = Carbon::now('prc')->toDateTimeString();
                }
            }
        }
        return $this->getModel()::query()->insert($data);
    }

    public function saveOrUpdate(array $whereFields, array $data): Model
    {
        return $this->getModel()::updateOrCreate($whereFields, $data);
    }

    public function updateById(object|string $id, array $data, string $segment = 'id'): model|null
    {
        try {
            if (is_object($id)) {
                $id = $id->$segment;
            }
            $model = $this->getModel()::query()->findOrFail($id);
            $model->update($data);
            return $model;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function updateBatchByIds(array $where, array $data): int
    {
        return $this->getModel()::where($where)->update($data);
    }

    public function removeById(object|string $id, string $segment = 'id'): void
    {
        try {
            if (is_object($id)) {
                $id = $id->$segment;
            }
            $model = $this->getModel()::query()->find($id);
            $model->delete();
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function removeByIds(array $ids): int
    {
        return $this->getModel()::destroy($ids);
    }

    public function page(int $current, int $pageSize, array $fields = ['*']): \Hyperf\Contract\LengthAwarePaginatorInterface
    {
        return $this->getModel()::select($fields)->paginate(perPage: $pageSize, page: $current);
    }

    public function queryPage(array $where = [], int $current = 1, int $pageSize = 115, array $fields = ['*']): \Hyperf\Contract\LengthAwarePaginatorInterface
    {
        return $this->getModel()::where($where)->select($fields)->paginate(perPage: $pageSize, page: $current);
    }

    public function queryPageByFilter(array $where = [], QueryFilter $filters = null, int $current = 1, int $pageSize = 15, array $fields = ['*']): \Hyperf\Contract\LengthAwarePaginatorInterface
    {
        return $this->getModel()::where($where)
            ->when($filters, function ($query) use ($filters) {
                return $query->filter($filters);
            })
            ->select($fields)->paginate(perPage: $pageSize, page: $current);
    }

    public function count(string $column = '*'): int
    {
        return $this->getModel()::count($column);
    }

    private function getPrimaryKeyValue()
    {
        try {
            return ApplicationContext::getContainer()->get(IdGeneratorInterface::class)->generate();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
}
