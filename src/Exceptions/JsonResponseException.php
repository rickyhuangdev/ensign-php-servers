<?php

declare(strict_types=1);

namespace Rickytech\Library\Exceptions;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Exception\QueryException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class JsonResponseException extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $responseContents = $response->getBody()->getContents();
        $responseContents = json_decode($responseContents, true);
        var_dump($responseContents);
        if (!empty($responseContents['error'])) {
            $port = null;
            $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
            $servers = $config->get('server.servers');
            foreach ($servers as $k => $server) {
                if ($server['name'] === 'jsonrpc-http') {
                    $port = $server['port'];
                    break;
                }
            }
            if ($responseContents['error']['data']['code'] === 0) {
                $responseContents['error']['code'] = 500;
            } elseif ($responseContents['error']['data']['code'] > 0) {
                $responseContents['error']['code'] = $responseContents['error']['data']['code'];
            }
            if ($throwable instanceof QueryException && env('APP_ENV') !== 'dev') {
                $responseContents['error']['message'] = 'Server Error';
            }
            $responseContents['error']['code'] = 500;
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
            "id"      => "1",
            "error"   => [
                "code"    => $code,
                "message" => $message,
                "data"    => null
            ]
        ];
    }
}
