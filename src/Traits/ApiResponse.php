<?php

namespace Rickytech\Library\Traits;

use Rickytech\Library\Exceptions\ApiResponseException;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;

trait ApiResponse
{
    protected $statusCode = FoundationResponse::HTTP_OK;

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
     * @param array|null $data
     * @param string $message
     * @return array
     */
    public function success(array|null $data, string $message): array
    {
        return $this->result(true, $data, $message, $this->statusCode);
    }

    /**
     * @param string $message
     * @param int $code
     * @return mixed
     * @throws ApiResponseException
     */
    public function error(string $message, int $code): never
    {
        throw new ApiResponseException($message, $code);
    }

    /**
     * @param bool $success
     * @param array|string|null $data
     * @param string|null $message
     * @param int $code
     * @return array
     */
    private function result(bool $success, array|string|null $data, string|null $message, int $code = 200): array
    {
        return [
            'success' => $success,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'errorMessage' => !$success ? $message : null,
            'errorCode' => !$success ? $code : null,
        ];
    }
}
