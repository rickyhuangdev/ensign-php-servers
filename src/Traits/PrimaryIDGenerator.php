<?php

namespace Rickytech\Library\Traits;

trait PrimaryIDGenerator
{
    /**
     * @throws \Exception
     */
    public function creating()
    {
        if (!$this->getKey()) {
            $this->{$this->getKeyName()} = (new PrimaryID(3))->getId();
        }
    }

    /**
     * @return bool
     */
    public function getIncrementing(): bool
    {
        return false;
    }
}
