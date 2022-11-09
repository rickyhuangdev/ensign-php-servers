<?php
declare(strict_types=1);

namespace Rickytech\Library\Exceptions;

use Throwable;

class ModelNotDefinedException extends \Exception
{
    public function __construct(string $message = "Mode does not defined", int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
