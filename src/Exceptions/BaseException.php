<?php

namespace Rickytech\Library\Exceptions;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

abstract class BaseException extends ExceptionHandler
{
    protected function getResponseResult(Throwable $throwable, ResponseInterface $response, int $code = 500): ResponseInterface
    {
        $message = $throwable->getMessage();

        if ($throwable instanceof ValidationException) {
            $message = $throwable->validator->errors()->first();
        }
        $this->stopPropagation();
        $data = json_encode([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'data' => null,
            'errorMessage' => $message,
            'errorCode' => $code,
        ]);
        return $response
            ->withAddedHeader('Content-Type', ' application/json; charset=UTF-8')
            ->withStatus($code)
            ->withBody(new SwooleStream($data));
    }
}
