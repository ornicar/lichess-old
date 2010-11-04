<?php

namespace Bundle\LichessBundle\Tests\Entities;

use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Chess\Clock;

class GameTest extends \PHPUnit_Framework_TestCase
{

    public function testCreation()
    {
        $game = new Game();
        $this->assertEquals('Bundle\LichessBundle\Entities\Game', get_class($game));
    }

    public function setPlayers()
    {
        $game = new Game();
        $game->setPlayers($this->createPlayerStubs());

        $this->assertEquals(2, count($game->getPlayers()));
    }

    public function testGetClockDoesNotCreateIt()
    {
        $game = new Game();
        $clock = $game->getClock();
        $this->assertNull($clock);
        $this->assertFalse($game->hasClock());
    }

    public function testSetCLock()
    {
        $game = new Game();
        $clock = $this->getClockMock();
        $game->setClock($clock);
        $this->assertSame($clock, $game->getClock());
        $this->assertTrue($game->hasClock());
    }

    protected function getClockMock()
    {
        return $this->getMock('Bundle\LichessBundle\Chess\Clock', array(), array(10*60*1000));
    }

    protected function createPlayerStubs()
    {
        $stubs = array();
        foreach(array('white', 'black') as $color) {
            $stubs[$color] = $this->getMock('Player', array());
        }

        return $stubs;
    }

}
