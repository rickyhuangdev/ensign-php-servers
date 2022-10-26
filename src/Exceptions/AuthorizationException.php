<?php

namespace Rickytech\Library\Exceptions;

use Rickytech\Library\Response\Result;
use Throwable;

class AuthorizationException extends \Exception
{
    public function __construct(string $message = "Unauthorized", int $code = Result::SESSION_EXPIRED, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
