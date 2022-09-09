<?php

namespace Rickytech\Library\Traits;

use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Utils\Collection;

trait TreeList
{
    public function toTreeList(array|Collection|LengthAwarePaginator $source, string $primaryKey = 'id', string $parentKey = 'pid', string $childrenKey = 'children'): array
    {
        if ($source instanceof LengthAwarePaginator) {
            $source = $source->getCollection();
        } elseif ($source instanceof Collection) {
            $source = $source->toArray();
        }
        $tree = [];
        $newData = array_column($source, null, $primaryKey);
        foreach ($newData as $key => &$value) {
            if ($value[$parentKey] > 0) {
                $newData[$value[$parentKey]][$childrenKey][] = &$value;
            } else {
                $tree[] = &$newData[$value[$primaryKey]];
            }
        }
        return $tree;
    }
}
