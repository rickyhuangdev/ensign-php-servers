<?php

namespace Rickytech\Library\Exceptions;

use Throwable;

class AuthorizationException extends \Exception
{
    public function __construct(string $message = "Unauthorized", int $code = 403, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
