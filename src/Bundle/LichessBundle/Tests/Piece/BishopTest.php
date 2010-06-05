<?php

namespace Bundle\LichessBundle\Tests\Piece;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Entities\Piece\Bishop;
use Bundle\LichessBundle\Entities\Piece;

require_once __DIR__.'/../gameBootstrap.php';

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
        $squares = $piece->getBasicTargetSquares();

        $this->assertEquals($expected, $board->squaresToKeys($squares));
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
        $squares = $piece->getBasicTargetSquares();
        $this->assertSquareKeys($expected, $board->squaresToKeys($squares));
    }

    protected function assertSquareKeys($expected, $result)
    {
        $this->assertEquals(array(), array_diff($expected, $result));
        $this->assertEquals(array(), array_diff($result, $expected));
    }

}
