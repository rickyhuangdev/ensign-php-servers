<?php
/**
 * Create by Ricky Huang
 * E-mail: ricky_huang_hkg@ensignfreight.com
 * Description: PageHelper
 * Date: 2023-03-09 12:00
 * Update: 2023-03-09 12:00
 */

namespace Rickytech\Library\Response;

use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Paginator\Paginator;

class PageHelper
{
    private Paginator $paginator;

    public function __construct(LengthAwarePaginatorInterface $data)
    {
        $this->paginator = new Paginator($data->items(), $data->perPage(), $data->currentPage());
    }

    public function getResult($resources, array $options = []): array
    {
        $data = [
            'item' => $resources->toArray(),
            'pageSize' => $this->paginator->perPage(),
            'current' => $this->paginator->currentPage(),
            'total' => $this->paginator->count(),
        ];
        if (!empty($options)) {
            return [...$data, ...$options];
        }
        return $data;
    }

    public function getCollectItems(): \Hyperf\Utils\Collection|\Illuminate\Support\Collection
    {
        return collect($this->paginator->items());
    }
}