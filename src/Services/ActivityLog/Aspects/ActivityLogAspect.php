<?php

declare(strict_types=1);

namespace Rickytech\Library\Services\ActivityLog\Aspects;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Psr\Container\ContainerInterface;
use Rickytech\Library\Services\ActivityLog\ActivityLogManager;
use Rickytech\Library\Services\ActivityLog\Attributes\ActivityLog;

#[Aspect]
class ActivityLogAspect extends AbstractAspect
{
    public array $classes = [];
    public array $annotations = [
        ActivityLog::class
    ];

    public function __construct(protected ContainerInterface $container, private ActivityLogManager $activityLogManager)
    {
    }

    /**
     * @throws Exception
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $result = $proceedingJoinPoint->process();
        if ($result) {
            $className = $proceedingJoinPoint->className;
            $methodName = $proceedingJoinPoint->methodName;
            $arguments = $proceedingJoinPoint->arguments['keys'];
            $this->activityLogManager->saveLog($className, $methodName, $arguments, $result);
        }
        return $result;
    }
}
