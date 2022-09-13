<?php

use \Hyperf\Utils\Collection;
use Hyperf\Paginator\LengthAwarePaginator;

if (!function_exists('convertArrayToTree')) {
    function convertArrayToTree(array|Collection|LengthAwarePaginator $data, $id = 0, $level = 0, string $primaryKey = 'id', string $parentKey = 'pid', string $childrenKey = 'children')
    {
        if ($data instanceof Collection) {
            $data = $data->toArray();
        } else if ($data instanceof LengthAwarePaginator) {
            $data = $data->getCollection()->toArray();
        }

        $list = array();
        foreach ($data as $k => $v) {
            if ($v[$parentKey] == $id) {
                $v['level'] = $level;
                $v[$childrenKey] = convertArrayToTree($data, $v["{$primaryKey}"], $level + 1);
                $list[] = $v;
            }
        }
        return $list;
    }
}