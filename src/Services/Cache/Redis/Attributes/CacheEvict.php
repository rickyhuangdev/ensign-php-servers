<?php

declare(strict_types=1);

namespace Rickytech\Library\Services\Cache\Redis\Attributes;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD)]
class CacheEvict extends AbstractAnnotation
{
    public function __construct(
        public ?string $prefix = null,
        public ?string $value = null,
        public bool $all = false,
        public string $group = 'default',
        public bool $collect = false
    ) {
    }
}
