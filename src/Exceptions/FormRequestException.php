<?php

namespace Rickytech\Library\Exceptions;

use Hyperf\Server\Exception\ServerException;
use Throwable;

class FormRequestException extends ServerException
{
    public function __construct(string $message = '', int $code = 422, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
