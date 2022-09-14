<?php
declare(strict_types=1);
namespace Rickytech\Library\Traits;

trait ClientResponse
{
    public function result(array $result): array
    {
        if ($result['code'] != 200) {
            throw new \RuntimeException($result['message']);
        }
        return $result;
    }
}
