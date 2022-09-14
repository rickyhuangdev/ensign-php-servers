<?php
declare(strict_types=1);
namespace Rickytech\Library\Traits;

use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Rickytech\Library\Exceptions\ApiResponseException;
use Hyperf\Paginator\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;

trait ApiResponse
{
    protected int $statusCode = FoundationResponse::HTTP_OK;

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     * @return ApiResponse
     */
    public function setStatusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param array|Model|Collection|LengthAwarePaginator|null $data
     * @param string|null $message
     * @return array
     */
    public function success(array|null|Model|Collection|LengthAwarePaginator $data, string|null $message = ''): array
    {
        return $this->result(true, $data, $message, $this->statusCode);
    }

    /**
     * @param string $message
     * @param ?int $code
     * @return never
     * @throws ApiResponseException
     */
    public function error(string $message, ?int $code = 400): never
    {
        throw new ApiResponseException($message, $code);
    }

    /**
     * @param bool $success
     * @param array|Model|Collection|LengthAwarePaginator|null $data
     * @param string|null $message
     * @param int $code
     * @return array
     */
    private function result(bool $success, array|null|Model|Collection|LengthAwarePaginator $data, string|null $message, int $code = 200): array
    {
        if ($data instanceof Collection || $data instanceof Model) {
            $data = $data->toArray();
        }
        if ($data instanceof LengthAwarePaginator) {
            return [
                'success' => true,
                'code' => $code,
                'message' => $message,
                'data' => $data->getCollection(),
                'total' => $data->total(),
                'current' => $data->currentPage(),
                'pageSize' => $data->perPage(),
                'totalPage' => $data->lastPage(),
                'errorMessage' => !$success ? $message : null,
                'errorCode' => !$success ? $code : null,

            ];
        }
        return [
            'success'      => $success,
            'code'         => $code,
            'message'      => $message,
            'data'         => $data,
            'errorMessage' => !$success ? $message : null,
            'errorCode'    => !$success ? $code : null,
        ];
    }
}
