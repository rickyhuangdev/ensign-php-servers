<?php

declare(strict_types=1);

namespace Rickytech\Library\Services\Cache\Redis\Aspects;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Rickytech\Library\Services\Cache\Redis\Attributes\CacheEvict;
use Rickytech\Library\Services\Cache\Redis\CacheAnnotation;
use Rickytech\Library\Services\Cache\Redis\CacheManager;

#[Aspect]
class CacheEvictAspect extends AbstractAspect
{
    public array $annotations = [
        CacheEvict::class
    ];

    public function __construct(protected CacheManager $manager, protected CacheAnnotation $cacheAnnotation)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];
        [$key] = $this->cacheAnnotation->getCacheEvictValue($className, $method, $arguments);
        $result = $proceedingJoinPoint->process();
        $this->manager->delete($key);
        return $result;
    }
}
