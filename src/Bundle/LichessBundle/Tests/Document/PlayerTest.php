<?php

namespace Bundle\LichessBundle\Document;

class PlayerTest extends \PHPUnit_Framework_TestCase
{

    public function testCreation()
    {
        $player = new Player('white');
        $this->assertEquals('Bundle\LichessBundle\Document\Player', get_class($player));
    }

    public function testStack()
    {
        $player = new Player('white');
        $stack = $player->getStack();
        $this->assertEquals(0, $player->getStack()->getVersion());
        $stack->addEvent(array('type' => 'test'));
        $this->assertEquals(1, $player->getStack()->getVersion());
    }

}
