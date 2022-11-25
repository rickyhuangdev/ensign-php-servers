<?php
declare(strict_types=1);

namespace Rickytech\Library\Traits;


trait IDGenerator
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
