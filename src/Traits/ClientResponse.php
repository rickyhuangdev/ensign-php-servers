<?php

namespace Rickytech\Library\Traits;

trait ClientResponse
{
    public function result(array $result)
    {
        if ($result['code'] != 200) {
            throw new \RuntimeException($result['message']);
        }
        return $result['data'];
    }
}