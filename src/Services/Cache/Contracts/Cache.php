<?php

namespace Rickytech\Library\Services\Cache\Contracts;

interface Cache
{
    public function remember($key, $ttl, \Closure $callback);

    public function has($key);

    public function get($key, $default = null);

    public function delete($key);

    public function clear();

    public function put($key, $value, int $ttl = 3600);
}