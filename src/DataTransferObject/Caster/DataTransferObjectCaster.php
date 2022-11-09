<?php

namespace Rickytech\Library\DataTransferObject\Caster;

use Rickytech\Library\DataTransferObject\Caster;
use Rickytech\Library\DataTransferObject\DataTransferObject;

class DataTransferObjectCaster implements Caster
{
    public function __construct(
        private array $classNames
    ) {
    }

    public function cast(mixed $value): DataTransferObject
    {
        foreach ($this->classNames as $className) {
            if ($value instanceof $className) {
                return $value;
            }
        }

        return new $this->classNames[0]($value);
    }
}
