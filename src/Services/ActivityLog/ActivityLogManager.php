<?php
declare(strict_types=1);
namespace Rickytech\Library\Services\ActivityLog;

use Hyperf\Database\Model\Model;
use Hyperf\Utils\ApplicationContext;
use ReflectionClass;
use ReflectionException;
use Rickytech\Library\DataTransferObject\DataTransferObject;
use Rickytech\Library\Services\ActivityLog\Attributes\ActivityLog;

class ActivityLogManager
{
    public function saveLog($className, string $methodName, mixed $arguments, mixed $result): void
    {
        $data = $this->getLogData($className, $methodName, $arguments, $result);
        $this->sendLogEvent($data);
    }

    /**
     * @throws ReflectionException
     */
    private function getLogData($className, string $methodName, mixed $arguments, mixed $result = null): array
    {
        $class = new ReflectionClass($className);
        $attributes = $class->getMethod($methodName)->getAttributes(ActivityLog::class);
        $argumentsArray = [];
        foreach ($arguments as $key => $argument) {
//            if ($argument instanceof DataTransferObject) {
//                $argument = $argument->toArray();
//            }
//
//            $argumentsArray['original_value'] = $argument;
//            if ($result instanceof OrgProfile) {
//                $argumentsArray['parent_module_id'] = $result->id;
//            } else {
//                $argumentsArray['parent_module_id'] = $argument['org_profile_id'] ?? $argument['id'] ?? '';
//            }
//            $argumentsArray['parent_module'] = env('APP_MODULE');
//            $argumentsArray['module_detail'] = $result instanceof Model ? $result->toArray() : array();
//            $argumentsArray = [...$argumentsArray, ...operationRequest()];
        }
        $activityData = [];
        foreach ($attributes as $attribute) {
            try {
                $attributeClass = $attribute->newInstance();
            } catch (\Exception $e) {
                continue;
            }
            if (!$attributeClass instanceof ActivityLog) {
                continue;
            }
            $activityData['description'] = $attributeClass->description;
            $activityData['action'] = !is_null($attributeClass->actionType) ?
                $attributeClass->actionType : (!is_null($argumentsArray['original_value']['id']) ?
                    SystemConstants::USER_OPERATION_UPDATE : SystemConstants::USER_OPERATION_CREATE);
        }
        return [...$argumentsArray, ...$activityData];
    }

    private function sendLogEvent(array $data): void
    {
        $message = new OperationLogProducer($data);
        $producer = ApplicationContext::getContainer()->get(Producer::class);
        $producer->produce($message);
    }
}
