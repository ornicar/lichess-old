<?php

namespace Bundle\LichessBundle\Tests\Piece;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Document\Piece\Pawn;
use Bundle\LichessBundle\Document\Piece;

class PawnTest extends \PHPUnit_Framework_TestCase
{
    public function testGetBoard()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $board = $game->getBoard();

        return $board;
    }

    /**
     * @depends testGetBoard
     */
    public function testGetBasicTargetSquaresFirstMove(Board $board)
    {
        $piece = $board->getPieceByKey('a2');
        $expected = array('a3', 'a4');
        $this->assertSquareKeys($expected, $piece->getBasicTargetKeys());
    }

    /**
     * @depends testGetBoard
     */
    public function testGetBasicTargetSquaresSecondMove(Board $board)
    {
        $piece = $board->getPieceByKey('a2');
        $piece->setY(4);
        $piece->setFirstMove(1);
        $board->compile();

        $expected = array('a5');
        $this->assertSquareKeys($expected, $piece->getBasicTargetKeys());
    }

    /**
     * @depends testGetBoard
     */
    public function testGetBasicTargetSquaresEat(Board $board)
    {
        $piece = $board->getPieceByKey('b2');
        $piece->setY('6');
        $board->compile();

        $expected = array('a7', 'c7');
        $this->assertSquareKeys($expected, $piece->getBasicTargetKeys());
        $this->assertSquareKeys($expected, $piece->getAttackTargetKeys());
    }

    /**
     * @depends testGetBoard
     */
    public function testGetBasicTargetSquaresEnPassant(Board $board)
    {
        $board->getGame()->setTurns(10);
        $piece = $board->getPieceByKey('f2');
        $piece->setY('5');
        $piece->setFirstMove(1);
        $board->getPieceByKey('g7')->setY(5);
        $board->getPieceByKey('g7')->setFirstMove(9);
        $board->compile();

        $expected = array('f6');
        $this->assertSquareKeys($expected, $piece->getBasicTargetKeys());
    }

    protected function assertSquareKeys($expected, $result)
    {
        $this->assertEquals(array(), array_diff($expected, $result));
        $this->assertEquals(array(), array_diff($result, $expected));
    }
}
