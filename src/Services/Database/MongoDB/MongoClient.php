<?php
/**
 * Create by Ricky Huang
 * E-mail: ricky_huang_hkg@ensignfreight.com
 * Description: MongoClient
 * Date: 2023-02-01 11:36
 * Update: 2023-02-01 11:36
 */

namespace Rickytech\Library\Services\Database\MongoDB;

class MongoClient
{
    protected string $database;
    protected string $collection;
    protected array $config;
    protected \MongoDB\Client $client;

    public function __construct()
    {
        if (!env("MONGODB_CONFIG")) {
            throw new \RuntimeException("Please provide a config file");
        }
        $this->client = new \MongoDB\Client(env("MONGODB_CONFIG"));
    }

    public function insertOne(array $document): \MongoDB\InsertOneResult
    {
        return $this->getCollection()->insertOne($document);
    }

    public function insertMany(array $documents): \MongoDB\InsertManyResult
    {
        return $this->getCollection()->insertMany($documents);
    }

    public function updateById(string $id, array $documents): \MongoDB\UpdateResult
    {
        return $this->getCollection()->updateOne(["_id" => new \MongoDB\BSON\ObjectId($id)], ['$set' => $documents]);
    }

    public function updateOrCreate(array $filter, array $document): object|array|null
    {
        return $this->getCollection()->findOneAndUpdate($filter, ['$set' => $document], ['upsert' => true]);
    }

    public function updateOne(array $filter, array $documents): \MongoDB\UpdateResult
    {
        return $this->getCollection()->updateOne($filter, ['$set' => $documents]);
    }

    public function updateMany(array $filter, array $documents): \MongoDB\UpdateResult
    {
        return $this->getCollection()->updateOne($filter, ['$set' => $documents]);
    }

    public function deleteOne(array $filter, array $documents): \MongoDB\DeleteResult
    {
        return $this->getCollection()->deleteOne($filter, $documents);
    }

    public function deleteMany(array $filter): \MongoDB\DeleteResult
    {
        return $this->getCollection()->deleteMany($filter);
    }

    public function findOneById(string $id): object|array|null
    {
        return $this->getCollection()->findOne(["_id" => new \MongoDB\BSON\ObjectId($id)]);
    }

    public function findOne(array $where): object|array|null
    {
        return $this->getCollection()->findOne($where);
    }

    public function findAll(array $where = []): \MongoDB\Driver\Cursor
    {
        return $this->getCollection()->find($where);
    }

    public function paginate(array $filter = [], $fields = [], int $page = 1, int $pageSize = 15, array $sort = [])
    {
        return $this->getCollection()->find($filter,
            $fields)->skip(($page - 1) * $pageSize)->limit($pageSize)->sort($sort);
    }

    public function totalCount($where = []): int
    {
        return $this->getCollection()->countDocuments($where);
    }

    private function getCollection()
    {
        return $this->client->{$this->database}->{$this->collection};
    }
}
