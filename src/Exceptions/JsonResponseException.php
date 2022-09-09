<?php

namespace Rickytech\Library\Exceptions;

use Hyperf\Database\Model\ModelNotFoundException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Throwable;

class JsonResponseException extends BaseException
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $responseContents = $response->getBody()->getContents();
        $responseContents = json_decode($responseContents, true);
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
            $responseContents['error']['message'] .= " - {$this->appName}:{$port}";
        }
        $data = json_encode($responseContents, JSON_UNESCAPED_UNICODE);
        return $response->withStatus(200)->withBody(new SwooleStream($data));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
