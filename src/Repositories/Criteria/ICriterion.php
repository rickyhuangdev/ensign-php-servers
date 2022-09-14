<?php

namespace Rickytech\Library\Repositories\Criteria;

interface ICriterion
{
    public function apply($model);
}
