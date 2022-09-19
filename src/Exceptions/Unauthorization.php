<?php

namespace Rickytech\Library\Exceptions;

use Throwable;

class Unauthorization extends \Exception
{
    public function __construct(string $message = "", int $code = 401, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}