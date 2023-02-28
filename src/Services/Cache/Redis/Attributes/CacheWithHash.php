<?php

namespace Rickytech\Library\Attributes;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD)]
class CacheWithHash extends AbstractAnnotation
{
    public function __construct(
        public ?string $prefix = null,
        public ?string $field = null,
        public ?string $value = null,
        public ?int    $ttl = null,
    ) {
    }
}
