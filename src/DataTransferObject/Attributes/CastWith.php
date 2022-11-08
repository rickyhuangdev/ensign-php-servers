<?php

namespace Rickytech\Library\DataTransferObject\Attributes;

use Attribute;
use Rickytech\Library\DataTransferObject\Caster;
use Rickytech\Library\DataTransferObject\Exceptions\InvalidCasterClass;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class CastWith
{
    public array $args;

    public function __construct(
        public string $casterClass,
        mixed ...$args
    ) {
        if (! is_subclass_of($this->casterClass, Caster::class)) {
            throw new InvalidCasterClass($this->casterClass);
        }

        $this->args = $args;
    }
}
