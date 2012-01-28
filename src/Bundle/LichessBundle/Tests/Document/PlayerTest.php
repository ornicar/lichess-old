<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Chess\Generator;

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

    public function testCompressPieces()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp




PPPPPPPP
RNBQKBNR
EOF;

        $game = $this->generate($data);
        $p = $game->getPlayer('white');
        $pieces = $p->getPieces();
        $p->compressPieces();
        $p->extractPieces();

        $this->assertEquals($pieces, $p->getPieces());
    }

    protected function generate($data)
    {
        $generator = new Generator();

        return $generator->createGameFromVisualBlock($data);
    }
}
