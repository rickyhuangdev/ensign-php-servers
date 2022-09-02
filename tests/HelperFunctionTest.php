<?php

namespace Rickytech\Library\Tests;

use PHPUnit\Framework\TestCase;

class HelperFunctionTest extends TestCase
{
    public function testGenerateCodeForNameFunction()
    {
        $data = ['name' => '','age'=>16];
        $str = createCodeForGivenName('hello', $data,5);
    }
}
