<?php
declare(strict_types=1);

namespace Rickytech\Library\Services\Models;


use Hyperf\DbConnection\Model\Model;
use Hyperf\Utils\Collection;
use Rickytech\Library\Filter\QueryFilter;

interface BaseMapperInterface
{
    public function getInstance(): ?Model;

    public function list(array $where, array $fields = ['*']): Collection;

    public function listByIds(array $ids, array $field = ['*']): Collection;

    public function getById(string $id, array $fields = ['*'], array $relations = []): Model|null;

    public function getByIds(array $ids, array $where = [], array $fields = ['*'], array $relations = []): Model|null;

    public function getOne(array $where, $fields = ['*'], array $relations = []);

    public function getOneOrFail(string $id, $fields = ['*']): \Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model|static;

    public function save(array $data): Model;

    public function saveBatch(array $data, $primaryKey = 'id', $autoIncrement = false, $autoInsertDate = true): bool;

    public function saveOrUpdate(array $whereFields, array $data): Model;

    public function updateById(object|string $id, array $data, string $segment = 'id'): model|null;

    public function updateBatchByIds(array $where, array $data): int;

    public function removeById(object|string $id, string $segment = 'id'): Model|null;

    public function removeByIds(array $ids): int;

    public function removeByQuery(array $where): int|bool|null;

    public function page(?int $current, ?int $pageSize, array $fields = ['*'], array $relations = []): \Hyperf\Contract\LengthAwarePaginatorInterface;

    public function queryPage(array $where = [], ?int $current = 1, ?int $pageSize = 115, array $fields = ['*'], array $relations = []): \Hyperf\Contract\LengthAwarePaginatorInterface;

    public function queryPageByFilter(array $where = [], QueryFilter|null $filters = null, ?int $current = 1, ?int $pageSize = 15, array $fields = ['*'], array $relations = [], array $withCount = []): \Hyperf\Contract\LengthAwarePaginatorInterface;

    public function count(string $column = '*', array $where = []): int;
}
