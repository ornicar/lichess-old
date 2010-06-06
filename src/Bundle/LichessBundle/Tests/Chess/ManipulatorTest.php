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
        $this->manipulator->play('a2 a4');
        $this->assertTrue($this->board->getPieceByKey('a4')->isClass('Pawn'));
        $this->assertTrue($this->board->getSquareByKey('a2')->isEmpty());
    }

    public function testMoveValidContinuous()
    {
        $this->manipulator->play('a2 a4');
        $this->board->getGame()->addturn();
        $this->manipulator->play('a4 a5');
        $this->board->getGame()->addturn();
        $this->manipulator->play('a5 a6');
        $this->board->getGame()->addturn();
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
        $this->manipulator->play('a2 a4');
    }

    /**
     * @expectedException LogicException
     */
    public function testMoveInvalidTo()
    {
        $this->manipulator->play('a2 a5');
    }

    public function testOpening()
    {
        $moves = array('e2 e4', 'e7 e5', 'g1 f3', 'b8 c6', 'f1 b5', 'a7 a6', 'b5 a4', 'g8 f6');
        foreach($moves as $move) {
            $this->manipulator->play($move);
        }
        $expected = <<<EOF

r bqkb r
 ppp ppp
p n  n  
    p   
B   P   
     N  
PPPP PPP
RNBQK  R

EOF;
        $this->assertEquals($expected, $this->board->dump());
    }

}
