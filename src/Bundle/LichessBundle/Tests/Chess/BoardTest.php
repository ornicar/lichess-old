<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Chess\Square;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Document as Entities;

class BoardTest extends \PHPUnit_Framework_TestCase
{

    public function testBoardCreation()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $board = $game->getBoard();

        $this->assertTrue($board instanceof Board);

        return $board;
    }

    /**
     * @depends testBoardCreation
     */
    public function testGetGame(Board $board)
    {
        $this->assertTrue($board->getGame() instanceof Entities\Game);
    }

    /**
     * @depends testBoardCreation
     */
    public function testGetSquares(Board $board)
    {
        $squares = $board->getSquares();
        $this->assertTrue(is_array($squares));
        $this->assertEquals(64, count($squares));
        $square = array_shift($squares);
        $this->assertTrue($square instanceof Square);
    }

    /**
     * @depends testBoardCreation
     */
    public function testGetSquareByKey(Board $board)
    {
        $key = 'a1';
        $square = $board->getSquareBykey($key);
        $this->assertTrue($square instanceof Square);
        $this->assertEquals('black', $square->getColor());
        $this->assertEquals(1, $square->getX());
        $this->assertEquals(1, $square->getY());
        $this->assertEquals($key, $square->getKey());

        $this->assertTrue($board->getSquareByKey('h8') instanceof Square);

        $this->assertEquals(null, $board->getSquareByKey('z9'));
    }

    /**
     * @depends testBoardCreation
     */
    public function testGetSquareByPos(Board $board)
    {
        $square = $board->getSquareByPos(1, 1);
        $this->assertSame($board->getSquareByKey('a1'), $square);
    }

    /**
     * @depends testBoardCreation
     */
    public function testGetPieceByKey(Board $board)
    {
        $piece = $board->getPieceByKey('a1');
        $this->assertTrue($piece instanceof Entities\Piece);
        $this->assertEquals('Rook', $piece->getClass());
        $this->assertEquals(1, $piece->getX());
        $this->assertEquals(1, $piece->getY());
        $this->assertEquals('white', $piece->getColor());
        $this->assertSame($board, $piece->getBoard());
    }

    /**
     * @depends testBoardCreation
     */
    public function testGetPieceByPos(Board $board)
    {
        $this->assertSame($board->getPieceByKey('a1'), $board->getPieceByPos(1, 1));
    }

    /**
     * @depends testBoardCreation
     */
    public function testSquaresToKeys(Board $board)
    {
        $squares = array(
            $board->getSquareByPos(1, 1),
            $board->getSquareByPos(3, 8)
        );

        $this->assertSame(array('a1', 'c8'), $board->squaresToKeys($squares));
    }

    /**
     * @depends testBoardCreation
     */
    public function testCleanSquares(Board $board)
    {
        $squares = array(
            $board->getSquareByPos(1, 1),
            null,
            $board->getSquareByPos(3, 8),
            $board->getSquareByPos(3, 8)
        );
        $cleanSquares = array(
            $board->getSquareByPos(1, 1),
            $board->getSquareByPos(3, 8)
        );
        $this->assertSame($cleanSquares, $board->cleanSquares($squares));
    }

    /**
     * @depends testBoardCreation
     */
    public function testPosToKey(Board $board)
    {
        $this->assertEquals('a1', $board->posToKey(1, 1));
        $this->assertEquals('h8', $board->posToKey(8, 8));
        $this->assertEquals('b4', $board->posToKey(2, 4));
    }

    /**
     * @depends testBoardCreation
     */
    public function testKeyToPos(Board $board)
    {
        $this->assertSame(array(1, 1), $board->keyToPos('a1'));
        $this->assertSame(array(8, 8), $board->keyToPos('h8'));
        $this->assertSame(array(2, 4), $board->keyToPos('b4'));
    }

    /**
     * @depends testBoardCreation
     */
    public function testDump(Board $board)
    {
        $expected = <<<EOF
rnbqkbnr
pppppppp




PPPPPPPP
RNBQKBNR
EOF;
        $this->assertEquals("\n".Generator::fixVisualBlock($expected)."\n", $board->dump());
    }
}
