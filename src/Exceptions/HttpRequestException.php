<?php
declare(strict_types=1);
namespace Rickytech\Library\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpRequestException extends BaseException
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // TODO: Implement handle() method.
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
