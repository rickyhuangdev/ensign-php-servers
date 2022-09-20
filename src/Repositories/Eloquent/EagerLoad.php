<?php

namespace Rickytech\Library\Repositories\Eloquent;

use Rickytech\Library\Repositories\Criteria\ICriterion;

class EagerLoad implements ICriterion
{
    public function __construct(protected $relationships)
    {
    }

    public function apply($model)
    {
        return $model->with($this->relationships);
    }
}