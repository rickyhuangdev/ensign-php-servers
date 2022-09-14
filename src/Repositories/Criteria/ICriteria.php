<?php
declare(strict_types=1);
namespace Rickytech\Library\Repositories\Criteria;

interface ICriteria
{
    public function withCriteria(...$criteria);
}
