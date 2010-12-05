<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Tests\ChessTest;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Model\Game;

abstract class ManipulatorAbstractTest extends ChessTest
{
    protected $game;
    protected $board;
    protected $manipulator;

    abstract public function getVariant();

    public function testMoveValid()
    {
        $this->manipulator->play('a2 a4');
        $this->assertTrue($this->board->getPieceByKey('a4')->isClass('Pawn'));
        $this->assertTrue($this->board->getSquareByKey('a2')->isEmpty());
    }

    public function testNextPossibleMoves()
    {
        $this->manipulator->play('b2 b4');
        $this->manipulator->play('a7 a5');
        $this->manipulator->play('b4 b5');
        $possibleMoves = $this->manipulator->play('c7 c5');
        $this->assertEquals(array('b6', 'c6'), $possibleMoves['b5']);
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
        $this->assertDump($expected);
    }

    public function testCastlingWhiteKingside()
    {
        $this->createGame($this->getWhiteCastlingData());
        $this->manipulator->move('e1 g1');
        $expected = <<<EOF
rnbqkbnr
pppppppp





R    RK
EOF;
        $this->assertDump($expected);
    }

    public function testCastlingWhiteQueenside()
    {
        $this->createGame($this->getWhiteCastlingData());
        $this->manipulator->move('e1 c1');
        $expected = <<<EOF
rnbqkbnr
pppppppp





  KR   R
EOF;
        $this->assertDump($expected);
    }

    protected function getWhiteCastlingData()
    {
        return <<<EOF
rnbqkbnr
pppppppp





R   K  R
EOF;
    }

    public function testCastlingBlackKingside()
    {
        $this->createGame($this->getBlackCastlingData(), true);
        $this->manipulator->move('e8 g8');
        $expected = <<<EOF
r    rk
pppppppp





R   K  R
EOF;
        $this->assertDump($expected);
    }

    public function testCastlingBlackQueenside()
    {
        $this->createGame($this->getBlackCastlingData(), true);
        $this->manipulator->move('e8 c8');
        $expected = <<<EOF
  kr   r
pppppppp





R   K  R
EOF;
        $this->assertDump($expected);
    }

    protected function getBlackCastlingData()
    {
        return <<<EOF
r   k  r
pppppppp





R   K  R
EOF;
    }

    public function setup()
    {
        $generator = $this->getGenerator();
        $this->game = $generator->createGame();
        $this->game->setVariant($this->getVariant());
        $this->board = $this->game->getBoard();
        $this->manipulator = $this->getManipulator($this->game);
    }

    /**
     * Get a game from visual data block
     *
     * @return Game
     **/
    protected function createGame($data, $blackTurn = false)
    {
        $generator = $this->getGenerator();
        $this->game = $generator->createGameFromVisualBlock($data);
        $this->game->setVariant($this->getVariant());
        $this->board = $this->game->getBoard();
        $this->game->setStatus(Game::STARTED);
        $this->game->setTurns($blackTurn ? 11 : 10);
        $this->manipulator = $this->getManipulator($this->game);
    }

    /**
     * Verify the game state
     *
     * @return void
     **/
    protected function assertDump($dump)
    {
        $dump = "\n".Generator::fixVisualBlock($dump)."\n";
        $this->assertEquals($dump, $this->board->dump());
    }
}
