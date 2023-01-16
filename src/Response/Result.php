<?php

declare(strict_types=1);

namespace Rickytech\Library\Response;

use Hyperf\Paginator\LengthAwarePaginator;
use Rickytech\Library\Constants\ResponseCode;

class Result
{
    public static function success($data = [])
    {
        return static::result(ResponseCode::SUCCESS, ResponseCode::getMessage(ResponseCode::SUCCESS), $data);
    }

    public static function error($message = '', $code = ResponseCode::ERROR, $data = [])
    {
        if (empty($message)) {
            return static::result($code, ResponseCode::getMessage($code), $data);
        } else {
//            return static::result($code, $message, $data);
            return [
                'success'   => false,
                'errorCode' => $code,
                'errorMsg'  => $message,
            ];
        }
    }

    protected static function result($code, $message, $data)
    {
        if ($data instanceof LengthAwarePaginator) {
            $data = [
                'items'     => $data->items(),
                'current'   => $data->currentPage(),
                'pageSize'  => $data->perPage(),
                'total'     => $data->total(),
                'totalPage' => $data->lastPage()
            ];
        }
        return [
            'success' => true,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ];
    }
}
