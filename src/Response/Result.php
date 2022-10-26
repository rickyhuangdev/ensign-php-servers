<?php

namespace Rickytech\Library\Response;

use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Utils\Collection as UtilCollection;

class Result
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

    public static function success(mixed $data = null, string|null $message = '', string|int $code = self::SUCCESS)
    {
        return self::result(true, $data, $message, $code);
    }

    public static function created(mixed $data, string|null $message = '', string|int $code = self::CREATED_SUCCESS)
    {
        return self::result(true, $data, $message, $code);
    }

    public static function updated(mixed $data, string|null $message = '', string|int $code = self::UPDATED_SUCCESS)
    {
        return self::result(true, $data, $message, $code);
    }

    public static function deleted(mixed $data, string|null $message = '', string|int $code = self::DELETE_SUCCESS)
    {
        return self::result(true, $data, $message, $code);
    }

    private static function result(bool $success, mixed $data, string|null $message, int $code = 200): array
    {
        if ($data instanceof Collection || $data instanceof Model || $data instanceof UtilCollection) {
            $data = $data->toArray();
        }
        if ($data instanceof LengthAwarePaginator || $data instanceof ResourceCollection) {
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
            'success' => $success,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'errorMessage' => !$success ? $message : null,
            'errorCode' => !$success ? $code : null,
        ];
    }
}