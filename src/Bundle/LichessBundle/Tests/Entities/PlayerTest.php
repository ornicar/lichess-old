<?php

namespace Bundle\LichessBundle\Tests\Entities;

use Bundle\LichessBundle\Entities\Player;

require_once __DIR__.'/../../Entities/Player.php';

class PlayerTest extends \PHPUnit_Framework_TestCase
{

    public function testCreation()
    {
        $player = new Player();
        $this->assertEquals('Bundle\LichessBundle\Entities\Player', get_class($player));
    }

    protected function createPieceStubs()
    {
        $stubs = array();
        
        for($it=0; $it<16; $it++) {
            $stubs[] = $this->getMock('Piece', array());
        }

        return $stubs;
    }

}
