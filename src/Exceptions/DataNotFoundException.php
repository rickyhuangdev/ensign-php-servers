<?php

namespace Rickytech\Library\Exceptions;

use Throwable;

class DataNotFoundException extends \Exception
{
    public function __construct(string $message = "Data Not Found", int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}