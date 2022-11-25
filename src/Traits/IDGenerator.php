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
            $snowflake = new \Godruoyi\Snowflake\Snowflake;
            $snowflake->setStartTimeStamp(strtotime('2022-01-01') * 1000); // millisecond
            $this->{$this->getKeyName()} = $snowflake->id();
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
