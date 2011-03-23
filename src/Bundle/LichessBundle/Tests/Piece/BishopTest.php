<?php

namespace Bundle\LichessBundle\Tests\Piece;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Document\Piece\Bishop;
use Bundle\LichessBundle\Document\Piece;

class BishopTest extends \PHPUnit_Framework_TestCase
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
        $piece = $board->getPieceByKey('c1');
        $expected = array();
        $this->assertEquals($expected, $piece->getBasicTargetKeys());
    }

    /**
     * @depends testGetBoard
     */
    public function testGetBasicTargetSquaresSecondMove(Board $board)
    {
        $piece = $board->getPieceByKey('c1');
        $piece->setX(3);
        $piece->setY(4);
        $board->compile();
        $expected = array('b3', 'b5', 'a6', 'd5', 'e6', 'f7', 'd3');
        $this->assertSquareKeys($expected, $piece->getBasicTargetKeys());
    }

    protected function assertSquareKeys($expected, $result)
    {
        $this->assertEquals(array(), array_diff($expected, $result));
        $this->assertEquals(array(), array_diff($result, $expected));
    }

}
