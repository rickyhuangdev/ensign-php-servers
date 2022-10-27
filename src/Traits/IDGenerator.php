<?php
declare(strict_types=1);

namespace Rickytech\Library\Traits;

use Ulid\Ulid;

trait IDGenerator
{
    /**
     * @throws \Exception
     */
    public function creating()
    {
        $ulid = Ulid::generate();
        if (!$this->getKey()) {
            $this->{$this->getKeyName()} = (string)$ulid;
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
