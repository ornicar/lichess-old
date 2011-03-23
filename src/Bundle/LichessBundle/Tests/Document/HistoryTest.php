<?php

namespace Bundle\LichessBundle\Document;

class HistoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $history = $this->createHistory();
        $this->assertEquals('joe', $history->getId());
    }
    public function testMaxElo()
    {
        $history = $this->createHistory();
        $this->assertEquals(1300, $history->getMaxElo());
    }

    public function testMaxEloDate()
    {
        $history = $this->createHistory();
        $this->assertEquals(5, $history->getMaxEloDate()->getTimestamp());
    }

    protected function createHistory()
    {
        $user = $this->getMockBuilder('Application\UserBundle\Document\User')
            ->setMethods(array('getUsernameCanonical', 'getCreatedAt'))
            ->getMock();
        $user->expects($this->once())
            ->method('getUsernameCanonical')
            ->will($this->returnValue('joe'));
        $user->expects($this->once())
            ->method('getCreatedAt')
            ->will($this->returnValue(new \DateTime()));

        $history = new History($user);
        foreach (array(
            1 => 1200,
            2 => 1250,
            3 => 1300,
            4 => 1250,
            5 => 1300,
            6 => 1100
        ) as $ts => $elo) {
            $history->addUnknownGame($ts, $elo);
        }

        return $history;
    }
}
