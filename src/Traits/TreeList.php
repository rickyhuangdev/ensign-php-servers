<?php
declare(strict_types=1);
namespace Rickytech\Library\Traits;

use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Utils\Collection;

trait TreeList
{
    public function toTreeList(array|Collection|LengthAwarePaginator $source, ?$id = 0, ?$level = 0, ?string $primaryKey = 'id', ?string $parentKey = 'pid', ?string $childrenKey = 'children', ?string $labelName, ?string $value): array
    {
        if ($source instanceof LengthAwarePaginator) {
            $source = $source->getCollection()->toArray();
        } elseif ($source instanceof Collection) {
            $source = $source->toArray();
        }
        $list = array();
        if ($labelName) {
            foreach ($source as $k => $v) {
                if ($v[$parentKey] == $id) {
                    $v['level'] = $level;
                    $v['label'] = $labelName;
                    $v['value'] = $value;
                    $v[$childrenKey] = $this->toTreeList($source, $v[$primaryKey], $level + 1);
                    $list[] = $v;
                }
            }
        } else {
            foreach ($source as $k => $v) {
                if ($v[$parentKey] == $id) {
                    $v['level'] = $level;
                    $v[$childrenKey] = $this->toTreeList($source, $v[$primaryKey], $level + 1);
                    $list[] = $v;
                }
            }
        }

        return $list;
    }

}
