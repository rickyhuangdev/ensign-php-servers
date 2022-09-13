<?php

declare(strict_types=1);

namespace Rickytech\Library\Exceptions;

use Hyperf\Contract\ConfigInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Rickytech\Library\Exceptions\ModelNotDefined;

class JsonResponseException extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $responseContents = $response->getBody()->getContents();
        $responseContents = json_decode($responseContents, true);
        if ($throwable instanceof ModelNotDefined) {
            return $response->withStatus(200)->
            withBody(new SwooleStream(json_encode(
                [
                    'success' => false,
                    'code' => 400,
                    'message' => $throwable->getMessage(),
                    'data' => null,
                    'errorMessage' => $throwable->getMessage(),
                    'errorCode' => 400,
                ]
            )));
        }
        if (!empty($responseContents['error'])) {
            $port = null;
            $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
            $servers = $config->get('server.servers');
            foreach ($servers as $k => $server) {
                if ($server['name'] == 'jsonrpc-http') {
                    $port = $server['port'];
                    break;
                }
            }
            if ($throwable->getCode()) {
                $responseContents['error']['code'] = $throwable->getCode();
            }
//            $responseContents['error']['message'] .= " - {$config->get('app_name')}:{$port}";
        }
        $data = json_encode($responseContents, JSON_UNESCAPED_UNICODE);
        return $response->withStatus(200)->withBody(new SwooleStream($data));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
