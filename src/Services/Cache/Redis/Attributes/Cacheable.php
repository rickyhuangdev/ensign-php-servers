<?php
declare(strict_types=1);

namespace Rickytech\Library\Services\Cache\Redis\Attributes;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD)]
class Cacheable extends AbstractAnnotation
{
    public function __construct(
        public ?string $prefix = null,
        public ?string $value = null,
        public ?int    $ttl = null,
    ) {
        parent::__construct($this->prefix,$this->value,$this->ttl);
    }
}
