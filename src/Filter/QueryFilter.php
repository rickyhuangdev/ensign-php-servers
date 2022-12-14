<?php
declare(strict_types=1);

namespace Rickytech\Library\Filter;

use Hyperf\Database\Model\Builder;

abstract class QueryFilter
{
    protected Builder $builder;

    public function __construct(protected array $data = [])
    {
    }

    public function filters(): array
    {
        return $this->data;
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->data as $name => $value) {
            if (method_exists($this, $name)) {
                if (is_null($value)) {
                    continue;
                }
                call_user_func_array([$this, $name], array_filter([$value]));
            }
        }
        return $this->builder;
    }

    protected function getInstrBuilder(string $field, string $keyword): Builder
    {
        return $this->builder->whereRaw("INSTR(`{$field}`, ?) > 0", [(string)$keyword]);
    }

    protected function getInstrBuilderRelation(string $relation, string $field, string $keyword): Builder
    {
        return $this->builder->whereHas((string)($relation), function ($query) use ($field, $keyword) {
            $query->whereRaw("INSTR(`{$field}`, ?) > 0", [(string)($keyword)]);
        });
    }

    protected function getBuilderRelationEqual(string $relation, string $field, string $keyword): Builder
    {
        return $this->builder->whereHas((string)($relation), function ($query) use ($field, $keyword) {
            $query->where((string)($field), '=', (string)($keyword));
        });
    }

    /**
     * @throws \JsonException
     */
    protected function sort($sortObject)
    {
        if (!empty($sortItems = json_decode($sortObject, true, 512, JSON_THROW_ON_ERROR))) {
            foreach ($sortItems as $itemName => $itemValue) {
//               if (method_exists($this, $itemName)) {
//                   call_user_func_array([$this, $itemName], array_filter([$itemValue]));
//               }
                $sort = $itemValue === 'ascend' ? 'ASC' : 'DESC';
                return $this->builder->orderBy("{$itemName}", "{$sort}");
            }
        }
    }
}
