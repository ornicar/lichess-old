<?php

namespace Bundle\LichessBundle\Tests\Piece;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Entities\Piece\Knight;
use Bundle\LichessBundle\Entities\Piece;

require_once __DIR__.'/../gameBootstrap.php';

class KnightTest extends \PHPUnit_Framework_TestCase
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
        $piece = $board->getPieceByKey('b1');
        $expected = array('a3', 'c3');
        $squares = $piece->getBasicTargetSquares();
        $squares = $board->cleanSquares($squares);

        $this->assertSquareKeys($expected, $board->squaresToKeys($squares));
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
        $squares = $piece->getBasicTargetSquares();
        $squares = $board->cleanSquares($squares);
        $this->assertSquareKeys($expected, $board->squaresToKeys($squares));
    }

    protected function assertSquareKeys($expected, $result)
    {
        $this->assertEquals(array(), array_diff($expected, $result));
        $this->assertEquals(array(), array_diff($result, $expected));
    }

}
