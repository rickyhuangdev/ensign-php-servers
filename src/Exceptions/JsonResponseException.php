<?php

declare(strict_types=1);

namespace Rickytech\Library\Exceptions;

use Hyperf\Contract\ConfigInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Rickytech\Library\Exceptions\ModelNotDefinedException;

class JsonResponseException extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $responseContents = $response->getBody()->getContents();
        $responseContents = json_decode($responseContents, true);
        var_dump($throwable->getLine(), $throwable->getMessage(), $throwable->getTraceAsString(), $throwable->getCode());
        if (!empty($responseContents['error'])) {
            var_dump($responseContents);
            $port = null;
            $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
            $servers = $config->get('server.servers');
            foreach ($servers as $k => $server) {
                if ($server['name'] == 'jsonrpc-http') {
                    $port = $server['port'];
                    break;
                }
            }
            $responseContents['error']['errorMessage'] = $responseContents['error']['data']['message'] ?? $throwable->getMessage();
            $responseContents['error']['code'] = $responseContents['error']['data']['code'];
//            $responseContents['error']['message'] .= " - {$config->get('app_name')}:{$port}";
        }
        $data = json_encode($responseContents, JSON_UNESCAPED_UNICODE);
        return $response->withStatus(200)->withBody(new SwooleStream($data));
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
