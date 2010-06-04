<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Chess\Square;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Entities as Entities;

require_once __DIR__.'/../../Entities/Game.php';
require_once __DIR__.'/../../Entities/Player.php';
require_once __DIR__.'/../../Entities/Piece.php';
require_once __DIR__.'/../../Entities/Piece/Pawn.php';
require_once __DIR__.'/../../Entities/Piece/Rook.php';
require_once __DIR__.'/../../Entities/Piece/Knight.php';
require_once __DIR__.'/../../Entities/Piece/Bishop.php';
require_once __DIR__.'/../../Entities/Piece/Queen.php';
require_once __DIR__.'/../../Entities/Piece/King.php';
require_once __DIR__.'/../../Chess/Generator.php';
require_once __DIR__.'/../../Chess/Board.php';
require_once __DIR__.'/../../Chess/Square.php';

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
        $key = 's11';
        $square = $board->getSquareBykey($key);
        $this->assertTrue($square instanceof Square);
        $this->assertEquals('black', $square->getColor());
        $this->assertEquals(1, $square->getX());
        $this->assertEquals(1, $square->getY());
        $this->assertEquals($key, $square->getKey());

        $this->assertTrue($board->getSquareByKey('s88') instanceof Square);

        $this->assertEquals(null, $board->getSquareByKey('s99'));
    }

    /**
     * @depends testBoardCreation
     */
    public function testGetSquareByPos(Board $board)
    {
        $square = $board->getSquareByPos(1, 1);
        $this->assertSame($board->getSquareByKey('s11'), $square);
    }

    /**
     * @depends testBoardCreation
     */
    public function testGetPieceByKey(Board $board)
    {
        $piece = $board->getPieceByKey('s11');
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
        $this->assertSame($board->getPieceByKey('s11'), $board->getPieceByPos(1, 1));
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

        $this->assertSame(array('s11', 's38'), $board->squaresToKeys($squares));
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
    public function testKeyToHumanPos(Board $board)
    {
        $this->assertEquals('a1', $board->keyToHumanPos('s11'));
        $this->assertEquals('h8', $board->keyToHumanPos('s88'));
        $this->assertEquals('b4', $board->keyToHumanPos('s24'));
    }

    /**
     * @depends testBoardCreation
     */
    public function testHumanPosToKey(Board $board)
    {
        $this->assertEquals('s11', $board->humanPosToKey('a1'));
        $this->assertEquals('s88', $board->humanPosToKey('h8'));
        $this->assertEquals('s24', $board->humanPosToKey('b4'));
    }
}
