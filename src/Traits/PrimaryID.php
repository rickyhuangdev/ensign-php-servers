<?php
declare(strict_types=1);
namespace Rickytech\Library\Traits;

use Swoole\Lock;

class PrimaryID
{
    const EPOCH = 1641137782000;

    const SEQUENCE_BITS = 12;
    const SEQUENCE_MAX = -1 ^ (-1 << self::SEQUENCE_BITS);

    const WORKER_BITS = 10;
    const WORKER_MAX = -1 ^ (-1 << self::WORKER_BITS);

    const TIME_SHIFT = self::WORKER_BITS + self::SEQUENCE_BITS;
    const WORKER_SHIFT = self::SEQUENCE_BITS;

    protected int $timestamp;
    protected $workerId;
    protected $sequence;
    protected $lock;

    public function __construct($workerId)
    {
        if ($workerId < 0 || $workerId > self::WORKER_MAX) {
            trigger_error("Worker ID 超出范围");
            exit(0);
        }

        $this->timestamp = 0;
        $this->workerId = $workerId;
        $this->sequence = 0;
        $this->lock = new Lock(SWOOLE_MUTEX);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        $this->lock->lock();
        $now = $this->now();
        if ($this->timestamp == $now) {
            $this->sequence++;

            if ($this->sequence > self::SEQUENCE_MAX) {
                while ($now <= $this->timestamp) {
                    $now = $this->now();
                }
            }
        } else {
            $this->sequence = 0;
        }

        $this->timestamp = $now;

        $id = (($now - self::EPOCH) << self::TIME_SHIFT) | ($this->workerId << self::WORKER_SHIFT) | $this->sequence;
        $this->lock->unlock();

        return $id;
    }

    /**
     * @return string
     */
    public function now(): string
    {
        return sprintf("%.0f", microtime(true) * 1000);
    }
}
