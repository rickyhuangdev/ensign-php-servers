<?php
declare(strict_types=1);
namespace Rickytech\Library\Exceptions;

use Throwable;

class ModelNotDefined extends \RuntimeException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
