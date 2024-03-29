<?php

declare(strict_types=1);

namespace Rickytech\Library\Response;

use Hyperf\Paginator\LengthAwarePaginator;
use Rickytech\Library\Constants\ResponseCode;

class Result
{
    public static function success($data = [], string $message = '')
    {
        return static::result(
            ResponseCode::SUCCESS,
            $message ?? ResponseCode::getMessage(ResponseCode::SUCCESS),
            $data
        );
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
        if (isset($data['data'])) {
            $data = $data['data'];
        }
        if ($data instanceof LengthAwarePaginator) {
            $data = [
                'columnFields' => $data->columnFields ?? [],
                'items'        => $data->items(),
                'current'      => $data->currentPage(),
                'pageSize'     => $data->perPage(),
                'total'        => $data->total(),
                'totalPage'    => $data->lastPage()
            ];
        } elseif ($data instanceof PageResult) {
            if (!$data->columnFields) {
                $data = [
                    'items'     => $data->items,
                    'current'   => $data->page,
                    'pageSize'  => $data->pageSize,
                    'total'     => $data->counts,
                    'totalPage' => $data->totalPages
                ];
            } else {
                $data = [
                    'columnFields' => $data->columnFields ?? [],
                    'items'        => $data->items,
                    'current'      => $data->page,
                    'pageSize'     => $data->pageSize,
                    'total'        => $data->counts,
                    'totalPage'    => $data->totalPages
                ];
            }
        }
        return [
            'success' => true,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ];
    }
}
