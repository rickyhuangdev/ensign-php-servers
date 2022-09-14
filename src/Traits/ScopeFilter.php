<?php
declare(strict_types=1);
namespace Rickytech\Library\Traits;

use Rickytech\Library\Filter\QueryFilter;

trait ScopeFilter
{
    public function scopeFilter($query, QueryFilter $filter): \Hyperf\Database\Model\Builder
    {
        return $filter->apply($query);
    }
}
