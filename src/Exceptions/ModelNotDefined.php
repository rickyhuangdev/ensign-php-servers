<?php

namespace Rickytech\Library\Exceptions;

class ModelNotDefined extends \InvalidArgumentException
{
    public static function named(string $className)
    {
        return new static("There is no model defined --- `{$className}`.");
    }
}