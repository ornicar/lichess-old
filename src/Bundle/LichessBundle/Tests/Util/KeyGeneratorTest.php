<?php

namespace Bundle\LichessBundle\Util;

class KeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateShortKey()
    {
        $key = KeyGenerator::generate(3);

        $this->assertEquals(3, strlen($key));
    }

    public function testGenerateLongKey()
    {
        $key = KeyGenerator::generate(30);

        $this->assertEquals(30, strlen($key));
    }

    public function testGenerateDifferentKeys()
    {
        $key1 = KeyGenerator::generate(12);
        $key2 = KeyGenerator::generate(12);

        $this->assertNotEquals($key1, $key2);
    }
}
