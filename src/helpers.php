<?php

declare(strict_types=1);

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Http\ServerFactory;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Collection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

if (!function_exists('convertArrayToTree')) {
    function convertArrayToTree(
        array|Collection|LengthAwarePaginator $data,
        $id = 0,
        $level = 0,
        string $primaryKey = 'id',
        string $parentKey = 'pid',
        string $childrenKey = 'children'
    ): array {
        if ($data instanceof Collection) {
            $data = $data->toArray();
        } elseif ($data instanceof LengthAwarePaginator) {
            $data = $data->getCollection()->toArray();
        }

        $list = array();
        foreach ($data as $k => $v) {
            if ($v[$parentKey] === $id) {
                $v['level'] = $level;
                $v[$childrenKey] = convertArrayToTree($data, $v[(string)($primaryKey)], $level + 1);
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
        return strtolower(ltrim(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $input), '_'));
    }
}
if (!function_exists('isJSON')) {
    /**
     * @throws JsonException
     */
    function isJSON($string): bool
    {
        return is_string($string) && is_array(json_decode(
            $string,
            true,
            512,
            JSON_THROW_ON_ERROR
        )) && (json_last_error() === JSON_ERROR_NONE);
    }
}

/**
 * 容器实例
 */
if (!function_exists('container')) {
    function container()
    {
        return ApplicationContext::getContainer();
    }
}

/**
 * redis 客户端实例
 */
if (!function_exists('redis')) {
    function redis()
    {
        return container()->get(Redis::class);
    }
}

/**
 * server 实例 基于 swoole server
 */
if (!function_exists('server')) {
    function server()
    {
        return container()->get(ServerFactory::class)->getServer()->getServer();
    }
}


/**
 * 缓存实例 简单的缓存
 */
if (!function_exists('cache')) {
    function cache()
    {
        return container()->get(Psr\SimpleCache\CacheInterface::class);
    }
}

/**
 * 控制台日志
 */
if (!function_exists('stdLog')) {
    function stdLog()
    {
        return container()->get(StdoutLoggerInterface::class);
    }
}

/**
 *
 */
if (!function_exists('request')) {
    function request()
    {
        return container()->get(ServerRequestInterface::class);
    }
}

/**
 *
 */
if (!function_exists('response')) {
    function response()
    {
        return container()->get(ResponseInterface::class);
    }
}
