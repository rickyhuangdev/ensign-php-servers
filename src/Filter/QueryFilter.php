<?php

namespace Rickytech\Library\Filter;

use Hyperf\Database\Model\Builder;

abstract class QueryFilter
{
    protected $builder;

    public function __construct(protected array $data = [])
    {
    }

    public function filters(): array
    {
        return $this->data;
    }

    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        foreach ($this->data as $name => $value) {
            if (method_exists($this, $name)) {
                call_user_func_array([$this, $name], array_filter([$value]));
            }
        }

        return $this->builder;
    }

    public function id($id)
    {
        return $this->builder->where('id', $id);
    }
}
