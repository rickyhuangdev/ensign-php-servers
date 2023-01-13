<?php

declare(strict_types=1);

namespace Rickytech\Library\DataTransferObject;

use Rickytech\Library\DataTransferObject\Validation\ValidationResult;

interface Validator
{
    public function validate(mixed $value): ValidationResult;
}
