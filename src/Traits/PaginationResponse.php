<?php
declare(strict_types=1);
namespace Rickytech\Library\Traits;

use Hyperf\Database\Model\Collection;
use Hyperf\Paginator\LengthAwarePaginator;

trait PaginationResponse
{
    public function paginateResponse($paginate):array
    {
        if ($paginate instanceof LengthAwarePaginator) {
            return [
                'success' => true,
                'total' => $paginate->total(),
                'current' => $paginate->currentPage(),
                'pageSize' => $paginate->perPage(),
                'totalPage' => $paginate->lastPage(),
                'data' => $paginate->getCollection(),
            ];
        }
        if ($paginate instanceof Collection) {
            $page = $paginate->toArray();
        }
        if (! is_array($paginate)) {
            return $paginate;
        }
        $total = count($paginate);
        return [
            'success' => true,
            'total' => $total,
            'page' => 1,
            'limit' => $total,
            'pages' => 1,
            'data' => $page,
        ];
    }
}
