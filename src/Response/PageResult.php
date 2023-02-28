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
    public int $totalPage;

    public function __construct(
        public int $current,
        public int $pageSize,
        public int $total,
        public array $items,
        public array|\MongoDB\Model\BSONDocument $columnFields
    ) {
        $this->totalPage = $this->total % $this->pageSize === 0 ? (int)($this->total / $this->pageSize) : (int)($this->total / $this->pageSize + 1);
    }
}
