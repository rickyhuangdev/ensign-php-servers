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
        return $this->response->json([
            'success' => true,
            'code' => $code,
            'data' => $data,
        ]);
    }

    public function fail(int $code, string $message = '')
    {
        return $this->response->json([
            'success' => false,
            'errorCode' => $code,
            'errorMsg' => $message,
        ]);
    }

    public function pageResult(mixed $data)
    {
//        if ($data instanceof Collection || $data instanceof Model || $data instanceof UtilCollection) {
//            $data = $data->toArray();
//        }
        if ($data instanceof LengthAwarePaginator || $data instanceof ResourceCollection || $data instanceof Paginator) {
            $data = [
                'items' => $data->getCollection() ?? $data->items(),
                'total' => $data->total() ?? $data->count(),
                'current' => $data->currentPage(),
                'pageSize' => $data->perPage(),
                'totalPage' => $data->lastPage() ?? 0,
            ];
        }

        if ($data instanceof LengthAwarePaginator || $data instanceof ResourceCollection || $data instanceof Paginator) {
            $data = [
                'items' => $data->getCollection() ?? $data->items(),
                'total' => $data->total() ?? $data->count(),
                'current' => $data->currentPage(),
                'pageSize' => $data->perPage(),
                'totalPage' => $data->lastPage() ?? 0,
            ];
        }

        return $this->response->json([
            'success' => true,
            'code' => 200,
            'data' => $data,
        ]);
    }
}
