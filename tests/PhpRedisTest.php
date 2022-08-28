<?php

namespace Rickytech\Library\Tests;

use PHPUnit\Framework\TestCase;
use Rickytech\Library\Services\Cache\Instances\PhpRedis;

class PhpRedisTest extends TestCase
{
    public function testUniqueInstance()
    {
        $firstCall = PhpRedis::getPhpRedis();
        $secondCall = PhpRedis::getPhpRedis();
        $this->assertInstanceOf(PhpRedis::class, $firstCall);
        $this->assertSame($firstCall, $secondCall);
    }
}
