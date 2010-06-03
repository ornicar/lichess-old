<?php

namespace Bundle\LichessBundle\Tests\Entities;

use Bundle\LichessBundle\Entities\Game;

require_once __DIR__.'/../../Entities/Game.php';

class GameTest extends \PHPUnit_Framework_TestCase
{

    public function testCreation()
    {
        $game = new Game();

        $this->assertEquals('Bundle\LichessBundle\Entities\Game', get_class($game));
    }
}
