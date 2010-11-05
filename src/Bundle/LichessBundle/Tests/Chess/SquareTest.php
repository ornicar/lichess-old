<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Chess\Square;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Document as Entities;

class SquareTest extends \PHPUnit_Framework_TestCase
{

    protected function createBoard()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $board = $game->getBoard();

        return $board;
    }

    protected function getSquare($x, $y, Board $board = null)
    {
        $board = $board ? $board : $this->createBoard();
        return $board->getSquareByPos($x, $y);
    }

    public function testGetPiece()
    {
        $square = $this->getSquare(1, 1);
        $piece = $square->getPiece();
        $this->assertTrue($piece instanceof Entities\Piece\Rook);
        $this->assertEquals('white', $piece->getColor());
        $this->assertEquals(1, $piece->getX());
        $this->assertEquals(1, $piece->getY());

        $this->assertEquals(null, $this->getSquare(1, 3)->getPiece());
    }

    public function testIsEmpty()
    {
        $this->assertTrue($this->getSquare(1, 3)->isEmpty());
        $this->assertFalse($this->getSquare(1, 1)->isEmpty());
    }

    public function testIsControlledBy()
    {
        $board = $this->createBoard();
        //$this->assertTrue($this->getSquare(1, 1, $board)->isControlledBy($board->getGame()->getPlayer('white')));
        //$this->assertTrue($this->getSquare(1, 8, $board)->isControlledBy($board->getGame()->getPlayer('black')));
        //$this->assertFalse($this->getSquare(1, 8, $board)->isControlledBy($board->getGame()->getPlayer('white')));
        //$this->assertFalse($this->getSquare(1, 3, $board)->isControlledBy($board->getGame()->getPlayer('white')));
    }
}
