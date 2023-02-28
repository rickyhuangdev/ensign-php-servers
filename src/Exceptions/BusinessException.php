<?php
declare(strict_types=1);
/**
 * Create by Ricky Huang
 * E-mail: ricky_huang_hkg@ensignfreight.com
 * Description: BusinessException
 * Date: 2023-01-09 09:12
 * Update: 2023-01-09 09:12
 */

namespace Rickytech\Library\Exceptions;

use Hyperf\Server\Exception\ServerException;
use Throwable;

class BusinessException extends ServerException
{
    public function __construct(string $message = "", int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
