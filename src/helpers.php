<?php
declare(strict_types=1);

use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Utils\Collection;

if (!function_exists('convertArrayToTree')) {
    function convertArrayToTree(array|Collection|LengthAwarePaginator $data, $id = 0, $level = 0, string $primaryKey = 'id', string $parentKey = 'pid', string $childrenKey = 'children')
    {
        if ($data instanceof Collection) {
            $data = $data->toArray();
        } elseif ($data instanceof LengthAwarePaginator) {
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

if (!function_exists('dashesToCamelCase')) {
    function dashesToCamelCase($string, $capitalizeFirstCharacter = false): array|string
    {
        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }
        return $str;
    }
}
if (!function_exists('camelCaseToUnderscore')) {
    function camelCaseToUnderscore($input): string
    {
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $input)), '_');
    }
}