<?php

declare(strict_types=1);

namespace Rickytech\Library\Services\Cache\Redis\Aspects;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Rickytech\Library\Services\Cache\Redis\Attributes\Cacheable;
use Rickytech\Library\Services\Cache\Redis\CacheAnnotation;
use Rickytech\Library\Services\Cache\Redis\CacheManager;

#[Aspect]
class CacheableAspect extends AbstractAspect
{
    public array $annotations = [
        Cacheable::class
    ];

    public function __construct(protected CacheManager $manager, protected CacheAnnotation $cacheAnnotation)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        var_dump(444);
        $className = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];
        [$key, $ttl] = $this->cacheAnnotation->getCacheableValue($className, $method, $arguments);
        [$has, $result] = $this->manager->get($key);
        if ($has) {
            return $result;
        }
        $result = $proceedingJoinPoint->process();
        $this->manager->set($key, $result, $ttl);
        return $result;
    }
}
