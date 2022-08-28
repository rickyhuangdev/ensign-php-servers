<?php

namespace Rickytech\Library\Exceptions;

use Hyperf\Database\Model\ModelNotFoundException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class JsonResponseException extends BaseException
{
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $responseContents = $response->getBody()->getContents();
        $responseContents = json_decode($responseContents, true);
        var_dump($responseContents);
        var_dump($throwable->getLine(), $throwable->getMessage(), $throwable->getTraceAsString(), $throwable->getCode(), $throwable->getPrevious());
        if ($throwable instanceof ModelNotFoundException) {
            $responseContents['error']['errorMessage'] = 'Sorry, the request data cannot be found';
            $responseContents['error']['errorCode'] = $responseContents['error']['code'] = 404;
        } else {
            $responseContents['error']['errorMessage'] = $responseContents['error']['message'] ?? $throwable->getMessage();
            $responseContents['error']['errorCode'] = $code ?? 500;
        }
        $data = json_encode($responseContents, JSON_UNESCAPED_UNICODE);
        return $response->withStatus(200)->withBody(new SwooleStream($data));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
