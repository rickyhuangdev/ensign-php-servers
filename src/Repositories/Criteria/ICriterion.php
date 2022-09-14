<?php
declare(strict_types=1);
namespace Rickytech\Library\Repositories\Criteria;

interface ICriterion
{
    public function apply($model);
}
