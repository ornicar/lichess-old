<?php

namespace Bundle\LichessBundle\Tests\Piece;

use Bundle\LichessBundle\Tests\ChessTest;
use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Model\Piece\King;

class KingTest extends ChessTest
{
    protected $board;

    public function setup()
    {
        $generator = $this->getGenerator();
        $game = $generator->createGame();
        $this->board = $game->getBoard();
    }

    public function testGetBasicTargetSquaresFirstMove()
    {
        $piece = $this->board->getPieceByKey('e1');
        $this->assertTrue($piece instanceof King);
        $expected = array();
        $this->assertSquareKeys($expected, $piece->getBasicTargetKeys());
    }

    public function testGetBasicTargetSquaresSecondMove()
    {
        $piece = $this->board->getPieceByKey('e1');
        $this->assertTrue($piece instanceof King);
        $piece->setX(3);
        $piece->setY(4);
        $piece->setFirstMove(1);
        $this->board->compile();
        $expected = array('b5', 'c5', 'd5', 'd4', 'd3', 'c3', 'b3', 'b4');
        $this->assertSquareKeys($expected, $piece->getBasicTargetKeys());
    }

    protected function assertSquareKeys($expected, $result)
    {
        $this->assertEquals(array(), array_diff($expected, $result));
        $this->assertEquals(array(), array_diff($result, $expected));
    }

}
