<?php
declare(strict_types=1);

namespace Rickytech\Library\Services\Cache\Redis;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Utils\Str;
use Rickytech\Library\Services\Cache\Redis\Attributes\Cacheable;
use Rickytech\Library\Services\Cache\Redis\Attributes\CacheEvict;
use Rickytech\Library\Services\Helpers\StringHelper;

class CacheAnnotation
{
    public function getCacheableValue(string $className, string $method, mixed $arguments): array
    {
        $annotation = $this->getAnnotation(Cacheable::class, $className, $method);
        $key = $this->getFormattedKey($annotation->prefix, $arguments, $annotation->value);
        $ttl = $annotation->ttl ?? 7200;
        return [$key, $ttl];
    }

    public function getCacheEvictValue(string $className, string $method, mixed $arguments): array
    {
        $annotation = $this->getAnnotation(CacheEvict::class, $className, $method);
        $prefix = $annotation->prefix;

        $key = $this->getFormattedKey($prefix, $arguments, $annotation->value);

        return [$key];
    }

    protected function getAnnotation(string $annotation, string $className, string $method): AbstractAnnotation
    {
        $collector = AnnotationCollector::get($className);
        $result = $collector['_m'][$method][$annotation] ?? null;
        if (!$result instanceof $annotation) {
            throw new \RuntimeException(sprintf('Annotation %s in %s:%s not exist.', $annotation, $className, $method));
        }

        return $result;
    }

    private function getFormattedKey($prefix, mixed $arguments, $value): string
    {
        return StringHelper::format($prefix, $arguments, $value);
    }
}
