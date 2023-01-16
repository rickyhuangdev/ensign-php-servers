<?php

declare(strict_types=1);

namespace Rickytech\Library\Response;

use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Paginator\Paginator;
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
        if ($data instanceof Paginator || $data instanceof LengthAwarePaginatorInterface) {
            $data = [
                'items'     => $data['data'],
                'current'   => $data['current_page'],
                'pageSize'  => $data['per_page'],
                'total'     => $data['total'],
                'totalPage' => $data['last_page'],
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
