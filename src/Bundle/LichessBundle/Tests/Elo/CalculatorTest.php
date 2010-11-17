<?php

namespace Bundle\LichessBundle\Elo;

class CalculatorTest extends \PHPUnit_Framework_TestCase
{
    public function testCalculate()
    {
        $calculator = new Calculator(32);
        $p1Elo = 1613;
        $p2Elo = 1388;
        $win = -1;
        list($newP1Elo, $newP2Elo) = $calculator->calculate($p1Elo, $p2Elo, $win);
        $this->assertEquals(1619.88, $newP1Elo);
        $this->assertEquals(1381.12, $newP2Elo);
        $this->assertEquals($newP1Elo - $p1Elo, -($newP2Elo - $p2Elo));
    }
}
