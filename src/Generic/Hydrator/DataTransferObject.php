<?php

namespace Rickytech\Library\Generic\Hydrator;

class DataTransferObject
{
    /**
     * @param array $array
     * @param object $object
     * @return object
     */
    public static function convertToObject(array $array, object $object): object
    {
        $class = get_class($object);
        $methodList = get_class_methods($class);
        foreach ($methodList as $method) {
            preg_match('/^(set)(.*?)$/i', $method, $matches);
            $prefix = $matches[1] ?? '';
            $key = $matches[2] ?? '';
            $key = strtolower(substr($key, 0, 1)) . substr($key, 1);
            if ($prefix == 'set' && !empty($array[$key])) {
                $object->$method($array[$key]);
            }
        }
        return $object;
    }

    /**
     * @param $object
     * @return array
     */
    public static function extract($object): array
    {
        $array = array();
        $class = get_class($object);
        $methodList = get_class_methods($class);
        foreach ($methodList as $method) {
            preg_match('/^(get)(.*?)$/i', $method, $matches);
            $prefix = $matches[1] ?? '';
            $key = $matches[2] ?? '';
            $key = strtolower(substr($key, 0, 1)) . substr($key, 1);
            if ($prefix == 'get') {
                $array[$key] = $object->$method();
            }
        }
        return $array;
    }
}
