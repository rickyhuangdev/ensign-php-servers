<?php

namespace Rickytech\Library\Exceptions;

class ModelNotDefined extends \RuntimeException
{
    public static function named(string $className)
    {
        return new static("There is no model defined --- `{$className}`.",400);
    }
}