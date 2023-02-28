<?php

declare(strict_types=1);

namespace Rickytech\Library\Services\Cache\Redis\Aspects;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Rickytech\Library\Services\Cache\Redis\Attributes\CacheWithHash;
use Rickytech\Library\Services\Cache\Redis\CacheAnnotation;
use Rickytech\Library\Services\Cache\Redis\CacheManager;

#[Aspect]
class CacheWithHashAspect extends AbstractAspect
{
    public array $annotations = [
        CacheWithHash::class
    ];

    public function __construct(protected CacheManager $manager, protected CacheAnnotation $cacheAnnotation)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];
        [$key, $field] = $this->cacheAnnotation->getHashCacheValue($className, $method, $arguments);
        [$has, $result] = $this->manager->hashGet($key, $field);
        if ($has) {
            return $result;
        }
        $result = $proceedingJoinPoint->process();
        $this->manager->hashSet($key, $field, $result);
        return $result;
    }
}
