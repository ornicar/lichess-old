<?php

namespace Bundle\LichessBundle\Elo;

use Application\UserBundle\Document\User;

class CalculatorTest extends \PHPUnit_Framework_TestCase
{
    public function testCalculate()
    {
        $calculator = new Calculator();
        $user1 = new User();
        $user1->setElo(1613);
        $user1->setNbRatedGames(56);
        $user2 = new User();
        $user2->setElo(1388);
        $user2->setNbRatedGames(356);
        $win = -1;
        list($newP1Elo, $newP2Elo) = $calculator->calculate($user1, $user2, $win);
        $this->assertEquals(1620, $newP1Elo);
        $this->assertEquals(1381, $newP2Elo);
        $this->assertEquals($newP1Elo - $user1->getElo(), -($newP2Elo - $user2->getElo()));
    }

    public function testCalculateWithProvision()
    {
        $calculator = new Calculator();
        $user1 = new User();
        $user1->setElo(1613);
        $user1->setNbRatedGames(8);
        $user2 = new User();
        $user2->setElo(1388);
        $user2->setNbRatedGames(356);
        $win = -1;
        list($newP1Elo, $newP2Elo) = $calculator->calculate($user1, $user2, $win);
        $this->assertEquals(1628, $newP1Elo);
        $this->assertEquals(1381, $newP2Elo);
    }

    public function testCalculateWithoutProvision()
    {
        $calculator = new Calculator();
        $user1 = new User();
        $user1->setElo(1313);
        $user1->setNbRatedGames(1256);
        $user2 = new User();
        $user2->setElo(1158);
        $user2->setNbRatedGames(124);
        $win = -1;
        list($newP1Elo, $newP2Elo) = $calculator->calculate($user1, $user2, $win);
        $this->assertEquals(1322, $newP1Elo);
        $this->assertEquals(1149, $newP2Elo);
    }

    public function testUserKfactor()
    {
        $calculator = new Calculator();
        $this->assertEquals(50, $calculator->nbRatedGamesToKfactor(0));
        $this->assertEquals(48, $calculator->nbRatedGamesToKfactor(1));
        $this->assertEquals(33, $calculator->nbRatedGamesToKfactor(10));
        $this->assertEquals(16, $calculator->nbRatedGamesToKfactor(20));
        $this->assertEquals(16, $calculator->nbRatedGamesToKfactor(200));
        $this->assertEquals(16, $calculator->nbRatedGamesToKfactor(1257));
    }
}
