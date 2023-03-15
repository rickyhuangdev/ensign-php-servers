<?php

declare(strict_types=1);

namespace Rickytech\Library\Exceptions;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class JsonResponseException extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $responseContents = $response->getBody()->getContents();
        $responseContents = json_decode($responseContents, true);
        $container = \Hyperf\Utils\ApplicationContext::getContainer();
        $logger = $container->get(StdoutLoggerInterface::class);
        $logger->warning(sprintf('message:[%s]', $throwable->getMessage()));
        $logger->warning(sprintf('code:[%s]', $throwable->getCode()));
        $logger->warning(sprintf('file:[%s]', $throwable->getFile()));
        $logger->warning(sprintf('line:[%s]', $throwable->getLine()));
        if ($throwable instanceof ValidationException) {
            $responseContents['error']['message'] = $throwable->validator->errors()->first();
            $responseContents['error']['code'] = 422;
        }
        if (!empty($responseContents['error'])) {
            if ($responseContents['error']['data']['code'] > 0) {
                $responseContents['error']['code'] = $responseContents['error']['data']['code'];
            }
        }
        $data = json_encode($responseContents, JSON_UNESCAPED_UNICODE);
        return $response->withAddedHeader(
            'Content-Type',
            ' application/json; charset=UTF-8'
        )->withStatus(200)->withBody(new SwooleStream($data));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

    /**
     * @param string $message
     * @param int $code
     * @return array
     */
    private function getJsonRpcDataFormat(string $message, int $code = 400): array
    {
        return [
            "jsonrpc" => "2.0",
            "id" => "1",
            "error" => [
                "code" => $code,
                "message" => $message,
                "data" => null
            ]
        ];
    }
}
