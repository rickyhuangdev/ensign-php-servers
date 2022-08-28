<?php

namespace Rickytech\Library\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class FromValidateException extends BaseException
{
    /**
     * @param Throwable $throwable
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        return $this->getResponseResult($throwable, $response, 422);
    }

    /**
     * @param Throwable $throwable
     * @return bool
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
