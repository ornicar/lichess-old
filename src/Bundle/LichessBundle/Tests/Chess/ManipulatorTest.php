<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;

require_once __DIR__.'/../gameBootstrap.php';
require_once __DIR__.'/../../Chess/Manipulator.php';

class ManipulatorTest extends \PHPUnit_Framework_TestCase
{
    protected $board;
    protected $manipulator;

    public function setup()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $this->board = $game->getBoard();
        $this->manipulator = new Manipulator($this->board);
    }

    public function testMoveValid()
    {
        $this->manipulator->move('a2 a4');
        $this->board->compile();
        $this->assertTrue($this->board->getPieceByKey('a4')->isClass('Pawn'));
        $this->assertTrue($this->board->getSquareByKey('a2')->isEmpty());
    }

    public function testMoveValidContinuous()
    {
        $this->manipulator->move('a2 a4');
        $this->board->compile();
        $this->manipulator->move('a4 a5');
        $this->board->compile();
        $this->manipulator->move('a5 a6');
        $this->board->compile();
        $this->assertTrue($this->board->getPieceByKey('a6')->isClass('Pawn'));
        $this->assertTrue($this->board->getSquareByKey('a2')->isEmpty());
        $this->assertTrue($this->board->getSquareByKey('a4')->isEmpty());
        $this->assertTrue($this->board->getSquareByKey('a5')->isEmpty());
    }

    /**
     * @expectedException LogicException
     */
    public function testMoveInvalidTurn()
    {
        $this->board->getGame()->setTurns(1);
        $this->manipulator->move('a2 a4');
    }

    /**
     * @expectedException LogicException
     */
    public function testMoveInvalidTo()
    {
        $this->manipulator->move('a2 a5');
    }
}
