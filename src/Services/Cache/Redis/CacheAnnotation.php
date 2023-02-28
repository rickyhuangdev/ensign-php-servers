<?php

declare(strict_types=1);

namespace Rickytech\Library\Services\Cache\Redis;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Utils\Str;
use Rickytech\Library\Services\Cache\Redis\Attributes\Cacheable;
use Rickytech\Library\Services\Cache\Redis\Attributes\CacheEvict;

class CacheAnnotation
{
    public function getCacheableValue(string $className, string $method, mixed $arguments)
    {
        $annotation = $this->getAnnotation(Cacheable::class, $className, $method);
        $key = $this->getFormattedKey($annotation->prefix, $arguments, $annotation->value);
        $ttl = $annotation->ttl ?? 7200;
        return [$key, $ttl];
    }

    public function getCacheEvictValue(string $className, string $method, mixed $arguments)
    {
        $annotation = $this->getAnnotation(CacheEvict::class, $className, $method);
        $prefix = $annotation->prefix;

        $key = $this->getFormattedKey($prefix, $arguments, $annotation->value);

        return [$key];
    }

    public function getHashCacheValue(string $className, string $method, mixed $arguments)
    {
        $annotation = $this->getAnnotation(CacheWithHash::class, $className, $method);
        $key = $this->getFormattedKey($annotation->prefix, $arguments, $annotation->value);
        return [$key, $annotation->field];
    }

    protected function getAnnotation(string $annotation, string $className, string $method): AbstractAnnotation
    {
        $collector = AnnotationCollector::get($className);
        $result = $collector['_m'][$method][$annotation] ?? null;
        if (!$result instanceof $annotation) {
            throw new CacheException(sprintf('Annotation %s in %s:%s not exist.', $annotation, $className, $method));
        }

        return $result;
    }

    private function getFormattedKey($prefix, mixed $arguments, $value)
    {
        return $this->format($prefix, $arguments, $value);
    }


    private function format(string $prefix, mixed $arguments, ?string $value = null): string
    {
        if ($value !== null) {
            if ($matches = $this->parse($value)) {
                foreach ($matches as $search) {
                    $k = str_replace(['#{', '}'], '', $search);
                    $value = Str::replaceFirst($search, (string)data_get($arguments, $k), $value);
                }
            }
        } else {
            $value = implode(':', $arguments);
        }

        return $prefix . ':' . $value;
    }


    private function parse(string $value): array
    {
        preg_match_all('/\#\{[\w\.]+\}/', $value, $matches);

        return $matches[0] ?? [];
    }
}
