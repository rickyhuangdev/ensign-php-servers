<?php
declare(strict_types=1);
namespace Rickytech\Library\Repositories\Eloquent;

use Hyperf\Database\Model\ModelNotFoundException;
use Hyperf\Utils\Arr;
use Rickytech\Library\Services\Cache\Instances\PhpRedis;
use Rickytech\Library\Traits\TreeList;
use Swoole\Database\MysqliException;
use App\Exception\ModelNotDefined;

abstract class BaseRepository
{
    use TreeList;
    protected $model;
    protected $cache;
    public function __construct()
    {
        $this->model = $this->getModelClass();
        $this->cache = PhpRedis::getPhpRedis();
        ;
    }

    protected function getModelClass()
    {
        if (!method_exists($this, 'model')) {
            throw new \RuntimeException('Model Not Defined', 500);
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
