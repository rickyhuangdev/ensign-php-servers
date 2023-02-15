<?php
/**
 * Create by Ricky Huang
 * E-mail: ricky_huang_hkg@ensignfreight.com
 * Description: PageResult
 * Date: 2023-02-15 16:19
 * Update: 2023-02-15 16:19
 */

namespace Rickytech\Library\Response;

class PageResult
{
    public int $totalPages;

    public function __construct(
        public int $page,
        public $pageSize,
        public int $counts,
        public array $items,
        public array $columnFields
    ) {
        $this->totalPages = $this->counts % $this->pageSize === 0 ? $this->counts / $this->pageSize : $this->counts / $this->pageSize + 1;
    }
}