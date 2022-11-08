<?php

namespace Rickytech\Library\DataTransferObject;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class MapFrom
{
    public function __construct(
        public string | int $name,
    ) {
    }
}
