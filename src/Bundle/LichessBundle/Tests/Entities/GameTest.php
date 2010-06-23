<?php

namespace Bundle\LichessBundle\Tests\Entities;

use Bundle\LichessBundle\Entities\Game;

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

    protected function createPlayerStubs()
    {
        $stubs = array();
        foreach(array('white', 'black') as $color) {
            $stubs[$color] = $this->getMock('Player', array());
        }

        return $stubs;
    }

}
