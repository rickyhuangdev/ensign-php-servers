<?php
declare(strict_types=1);

namespace Rickytech\Library\Response;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class Result1
{
    const SUCCESS = 200001;
    const CREATED_SUCCESS = 200002;
    const UPDATED_SUCCESS = 200003;
    const DELETE_SUCCESS = 200004;
    const REQUEST_FAILED = 400000;
    const VALIDATE_FAILED = 400001;
    const DATA_NOTFOUND = 400002;
    const SESSION_EXPIRED = 400003;
    const FORBIDDEN = 400004;
    const SERVER_ERROR = 500000;
    const MSG_SUCCESS = 'success';
    const MSG_CREATED_SUCCESS = 'Created successfully';
    const MSG_UPDATED_SUCCESS = 'Updated successfully';
    const MSG_DELETE_SUCCESS = 'Deleted successfully';
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
            'code' => $code,
            'message' => $message,
        ]);
    }

    public function created(mixed $data, string|null $message = '', string|int $code = self::CREATED_SUCCESS)
    {
        return $this->result(true, $data, $message, $code);
    }

    public function updated(mixed $data, string|null $message = '', string|int $code = self::UPDATED_SUCCESS)
    {
        return $this->result(true, $data, $message, $code);
    }

    public function deleted(mixed $data, string|null $message = '', string|int $code = self::DELETE_SUCCESS)
    {
        return $this->result(true, $data, $message, $code);
    }

    private function result(bool $success, mixed $data, string|null $message, int $code = 200)
    {
        $response = [
            'success' => $success,
            'code' => $code,
            'data' => $data,
            'message' => $message
        ];
//        $responseData = null;
//        if ($data instanceof Collection || $data instanceof Model || $data instanceof UtilCollection) {
//            $responseData = $data->toArray();
//        }
//        if ($data instanceof LengthAwarePaginator || $data instanceof ResourceCollection || $data instanceof Paginator) {
//            $responseData = [
//                'items' => $data->getCollection() ?? $data->items(),
//                'total' => $data->total() ?? $data->count(),
//                'current' => $data->currentPage(),
//                'pageSize' => $data->perPage(),
//                'totalPage' => $data->lastPage() ?? 0,
//            ];
//        }
//        if ($data) {
//            return $this->response->json([...$response, 'data' => $responseData]);
//        }
        return $this->response->json($response);
    }
}
