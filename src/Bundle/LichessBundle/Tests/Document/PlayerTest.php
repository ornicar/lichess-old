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

    public function testCompressPiecesStart()
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

    public function testCompressPiecesMoved()
    {
        $data = <<<EOF
       k


pP



K
EOF;

        $game = $this->generate($data, 20);
        $game->getBoard()->getPieceByKey('a5')->setFirstMove(14);
        $game->getBoard()->getPieceByKey('b5')->setFirstMove(3);
        $game->removeDependencies();
        $p = $game->getPlayer('white');
        $pieces = $p->getPieces();
        $p->compressPieces();
        $p->extractPieces();

        $this->assertEquals($pieces, $p->getPieces());
    }

    public function testCompressPiecesDead()
    {
        $data = <<<EOF
       k


pP



K
EOF;

        $game = $this->generate($data, 20);
        $game->getBoard()->getPieceByKey('a5')->setFirstMove(14);
        $game->getBoard()->getPieceByKey('b5')->setFirstMove(3);
        $game->getBoard()->getPieceByKey('b5')->setIsDead(true);
        $game->removeDependencies();
        $p = $game->getPlayer('white');
        $pieces = $p->getPieces();
        $p->compressPieces();
        $p->extractPieces();

        $this->assertEquals($pieces, $p->getPieces());
    }

    protected function generate($data, $turns = 8)
    {
        $generator = new Generator();

        $game = $generator->createGameFromVisualBlock($data);
        $game->setTurns($turns);

        return $game;
    }
}
