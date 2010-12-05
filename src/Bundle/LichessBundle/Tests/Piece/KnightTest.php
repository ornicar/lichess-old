<?php

namespace Bundle\LichessBundle\Tests\Piece;

use Bundle\LichessBundle\Tests\ChessTest;
use Bundle\LichessBundle\Chess\Board;

class KnightTest extends ChessTest
{
    public function testGetBoard()
    {
        $generator = $this->getGenerator();
        $game = $generator->createGame();
        $board = $game->getBoard();

        return $board;
    }

    /**
     * @depends testGetBoard
     */
    public function testGetBasicTargetSquaresFirstMove(Board $board)
    {
        $piece = $board->getPieceByKey('b1');
        $expected = array('a3', 'c3');
        $this->assertSquareKeys($expected, $piece->getBasicTargetKeys());
    }

    /**
     * @depends testGetBoard
     */
    public function testGetBasicTargetSquaresSecondMove(Board $board)
    {
        $piece = $board->getPieceByKey('b1');
        $piece->setX(3);
        $piece->setY(4);
        $board->compile();
        $expected = array('a3', 'a5', 'b6', 'd6', 'e5', 'e3');
        $this->assertSquareKeys($expected, $piece->getBasicTargetKeys());
    }

    protected function assertSquareKeys($expected, $result)
    {
        $this->assertEquals(array(), array_diff($expected, $result));
        $this->assertEquals(array(), array_diff($result, $expected));
    }

}
