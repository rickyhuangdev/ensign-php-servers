<?php

namespace Rickytech\Library\Tests;

use PHPUnit\Framework\TestCase;
use Rickytech\Library\Traits\PrimaryID;

class HelperFunctionTest extends TestCase
{
    public function testGenerateCodeForNameFunction()
    {
        $data = ['name' => '','age'=>16];
        $str = createCodeForGivenName('hello', $data,5);
        
        var_dump((new PrimaryID(3))->getId());
    }
}
