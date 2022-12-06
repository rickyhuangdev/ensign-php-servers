<?php
declare(strict_types=1);

namespace Rickytech\Library\Services\Database\Dao;

use Carbon\Carbon;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Snowflake\IdGeneratorInterface;
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

    protected function getModel(): Model|null
    {
        return make($this->setModel(), ['enableCache' => true]);
    }

    public function list(array $where, array $fields = ['*']): Collection|null
    {
        return $this->getModel()::query()->when($where, function ($query) use ($where) {
            return $query->where($where);
        })->select($fields)->get();
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

    public function getOneOrFail(string $id, $fields = ['*']): \Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model|static
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
                if (!Arr::exists($value, $primaryKey)) {
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

        if (is_object($id)) {
            $id = $id->$segment;
        }
        $model = $this->getModel()::query()->findOrFail($id);
        $model->update($data);
        return $model;

    }

    public function updateBatchByIds(array $where, array $data): int
    {
        return $this->getModel()::where($where)->update($data);
    }

    public function removeById(object|string $id, string $segment = 'id'): Model|null
    {

        if (is_object($id)) {
            $id = $id->$segment;
        }
        $model = $this->getModel()::query()->findOrFail($id);
        $model->delete();
        return $model;

    }

    public function removeByIds(array $ids): int
    {
        return $this->getModel()::destroy($ids);
    }

    public function removeByQuery(array $where): int|bool|null
    {
        return $this->getModel()::query()->where($where)->delete();
    }

    public function page(?int $current, ?int $pageSize, array $fields = ['*'], array $relations = []): \Hyperf\Contract\LengthAwarePaginatorInterface
    {
        return $this->getModel()::select($fields)
            ->when($relations, function ($query) use ($relations) {
                $query->with($relations);
            })
            ->paginate(perPage: $pageSize ?? 15, page: $current ?? 1);
    }

    public function queryPage(array $where = [], ?int $current = 1, ?int $pageSize = 15, array $fields = ['*'], array $relations = []): \Hyperf\Contract\LengthAwarePaginatorInterface
    {
        return $this->getModel()::query()
            ->where($where)
            ->when($relations, function ($query) use ($relations) {
                $query->with($relations);
            })
            ->select($fields)->paginate(perPage: $pageSize ?? 15, page: $current ?? 1);
    }

    public function queryPageByFilter(array $where = [], QueryFilter|null $filters = null, ?int $current = 1, ?int $pageSize = 15, array $fields = ['*'], array $relations = [], array $withCount = []): \Hyperf\Contract\LengthAwarePaginatorInterface
    {
        return $this->getModel()::query()
            ->where($where)
            ->when($relations, function ($query) use ($relations) {
                $query->with($relations);
            })
            ->when($withCount, function ($query) use ($withCount) {
                $query->withCount($withCount);
            })
            ->when(is_object($filters), function ($query) use ($filters) {
                return $query->filter($filters);
            })
            ->select($fields)
            ->paginate(perPage: $pageSize ?? 15, page: $current ?? 1);
    }

    public function count(string $column = '*', array $where = []): int
    {
        return $this->getModel()::query()
            ->when($where, function ($query) use ($where) {
                return $query->where($where);
            })->count($column);
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
