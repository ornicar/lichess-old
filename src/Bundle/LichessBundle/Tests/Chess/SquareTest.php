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

    protected function createBoard()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $board = $game->getBoard();

        $this->assertTrue($board instanceof Board);

        return $board;
    }

    protected function getSquare($x, $y)
    {
        return $this->createBoard()->getSquareByPos($x, $y);
    }

    /**
     * @depends testBoardCreation
     */
    public function testGetPiece(Board $board)
    {
        $square = $this->getSquare(1, 1);
        $piece = $square->getPiece();
        $this->assertTrue($piece instanceof Rook);
        $this->assertEquals('white', $piece->geColor());
        $this->assertEquals(1, $piece->getX());
        $this->assertEquals(1, $piece->getY());
    }
}
