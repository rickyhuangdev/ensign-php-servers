<?php
declare(strict_types=1);

namespace Rickytech\Library\Traits;

use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Utils\Collection;

trait TreeList
{
    public function toTreeList(array|Collection|LengthAwarePaginator $source, $id = 0, $level = 0, ?string $primaryKey = 'id', ?string $parentKey = 'pid', ?string $childrenKey = 'children'): array
    {
        if ($source instanceof LengthAwarePaginator) {
            $source = $source->getCollection()->toArray();
        } elseif ($source instanceof Collection) {
            $source = $source->toArray();
        }
        $list = array();
        foreach ($source as $k => $v) {
            if ($v[$parentKey] == $id) {
                $v['level'] = $level;
                $v[$childrenKey] = $this->toTreeList($source, $v[$primaryKey], $level + 1);
                $list[] = $v;
            }
        }


        return $list;
    }

    public function listToTreeByReference(
        array $source,
        ?string $indexKey = 'id',
        ?string $parentKey = 'pid',
        ?string $childrenKey = 'children'
    ): array {
        $items = array_column($source, null, $indexKey);
        $tree = [];
        foreach ($items as $key => $value) {
            if (isset($items[$value["{$parentKey}"]])) {
                $items[$value["{$parentKey}"]]["{$childrenKey}"][] = &$items[$key];
            } else {
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }
}
