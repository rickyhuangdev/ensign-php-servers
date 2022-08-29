<?php

namespace Rickytech\Library\Traits;

trait TreeList
{
    public function toTreeList(array $source, string $primaryKey = 'id', string $parentKey = 'pid', string $childrenKey = 'children'): array
    {
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