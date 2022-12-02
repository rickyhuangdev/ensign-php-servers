<?php

namespace Rickytech\Library\Services\ActivityLog\Attributes;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class ActivityLog extends AbstractAnnotation
{
    public function __construct(
        public ?string $description,
        public ?string $actionType = null
    ) {
    }
}
