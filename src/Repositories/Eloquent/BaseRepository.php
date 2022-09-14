<?php

namespace Rickytech\Library\Repositories\Eloquent;

use Hyperf\Database\Model\ModelNotFoundException;
use Hyperf\Utils\Arr;
use Swoole\Database\MysqliException;
use Rickytech\Library\Exceptions\ModelNotDefined;
abstract class BaseRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = $this->getModelClass();
    }

    protected function getModelClass()
    {
        if (!method_exists($this, 'model')) {
            throw ModelNotDefined::named('No model defined');
        }
        return new ($this->model())();
    }

    public function all(?array $data = [])
    {
        return $this->model->get();
    }

    public function findById(string|int $id)
    {
        try {
            return $this->model->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new \RuntimeException('The requested resource was not found on this server', 404);
        }
    }

    public function create(array $data)
    {
        try {
            return $this->model->create($data);
        } catch (MysqliException $e) {
            throw new \RuntimeException($e->getMessage(), 500);
        }
    }

    public function update(array $data, string|int $id)
    {
        $model = $this->findById($id);
        $model->update($data);
        return $model;
    }

    public function delete(string|int $id)
    {
        $model = $this->findById($id);
        $model->delete();
        return $model;
    }

    public function paginate(?array $data = [], int $current = 1, int $pageSize = 15)
    {

        return $this->model->paginate(perPage: $data['pageSize'] ?? $pageSize, page: $data['current'] ?? $current);
    }

    public function withCriteria(...$criteria): BaseRepository
    {
        $criteria = Arr::flatten($criteria);
        foreach ($criteria as $criterion) {
            $this->model = $criterion->apply($this->model);
        }
        return $this;
    }
}