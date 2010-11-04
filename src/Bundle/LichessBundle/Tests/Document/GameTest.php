<?php

namespace Bundle\LichessBundle\Document;

class GameTest extends \PHPUnit_Framework_TestCase
{

    public function testCreation()
    {
        $game = new Game();
        $this->assertEquals('Bundle\LichessBundle\Document\Game', get_class($game));
    }

    public function testSetPlayers()
    {
        $game = new Game();
        $players = $this->createPlayerStubs();
        $game->setPlayer($players['white']);
        $game->setPlayer($players['black']);

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
        return $this->getMock('Bundle\LichessBundle\Document\Clock', array(), array(10*60*1000));
    }

    protected function createPlayerStubs()
    {
        $stubs = array();
        foreach(array('white', 'black') as $color) {
            $stubs[$color] = $this->getMock('Bundle\LichessBundle\Document\Player', array(), array($color));
        }

        return $stubs;
    }

}
