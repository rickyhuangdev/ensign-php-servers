<?php

declare(strict_types=1);

namespace Rickytech\Library\Response;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class Result1
{
    const SUCCESS = 200001;
    private ContainerInterface $container;
    /**
     * @var mixed|ResponseInterface
     */
    private mixed $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(ResponseInterface::class);
    }

    public function success(mixed $data = null, string|null $message = '', string|int $code = self::SUCCESS)
    {
        if (isset($data['data'], $data['current_page'], $data['per_page'])) {
            return $this->pageResult($data);
        }
        return $this->response->json([
            'success' => true,
            'code'    => $code,
            'data'    => $data,
        ]);
    }

    public function fail(int $code, string $message = '')
    {
        return $this->response->json([
            'success'   => false,
            'errorCode' => $code,
            'errorMsg'  => $message,
        ]);
    }

    public function pageResult(mixed $data)
    {
        return $this->response->json([
            'success' => true,
            'code'    => 200,
            'data'    => [
                'items'     => $data['data'],
                'total'     => $data['total'],
                'current'   => $data['current_page'],
                'pageSize'  => $data['per_page'],
                'totalPage' => $data['last_page'],
            ],
        ]);
    }
}
