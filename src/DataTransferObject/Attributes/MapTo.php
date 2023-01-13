<?php

declare(strict_types=1);

namespace Rickytech\Library\DataTransferObject\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class MapTo
{
    public function __construct(
        public string $name,
    ) {
    }
}
