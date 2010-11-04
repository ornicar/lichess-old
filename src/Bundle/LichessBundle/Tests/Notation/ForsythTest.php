<?php

namespace Bundle\LichessBundle\Tests\Notation;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Notation\Forsyth;

class ForsythTest extends \PHPUnit_Framework_TestCase
{
    public function testExport()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $manipulator = new Manipulator($game);
        $forsyth = new Forsyth();
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', $forsyth->export($game));
        $manipulator->play('e2 e4');
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1', $forsyth->export($game));
        $manipulator->play('c7 c5');
        $this->assertEquals('rnbqkbnr/pp1ppppp/8/2p5/4P3/8/PPPP1PPP/RNBQKBNR w KQkq c6 0 2', $forsyth->export($game));
        $manipulator->play('g1 f3');
        $this->assertEquals('rnbqkbnr/pp1ppppp/8/2p5/4P3/5N2/PPPP1PPP/RNBQKB1R b KQkq - 1 2', $forsyth->export($game));
        $manipulator->play('g8 h6');
        $this->assertEquals('rnbqkb1r/pp1ppppp/7n/2p5/4P3/5N2/PPPP1PPP/RNBQKB1R w KQkq - 2 3', $forsyth->export($game));
        $manipulator->play('a2 a3');
        $this->assertEquals('rnbqkb1r/pp1ppppp/7n/2p5/4P3/P4N2/1PPP1PPP/RNBQKB1R b KQkq - 0 3', $forsyth->export($game));
    }

    public function testExportCastling()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $game->getBoard()->getPieceByKey('a1')->setFirstMove(1);
        $game->getBoard()->getPieceByKey('h8')->setFirstMove(1);
        $manipulator = new Manipulator($game);
        $forsyth = new Forsyth();
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w Kq - 0 1', $forsyth->export($game));
        $game->getBoard()->getPieceByKey('a8')->setFirstMove(1);
        $game->getBoard()->getPieceByKey('h1')->setFirstMove(1);
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w - - 0 1', $forsyth->export($game));
    }

    public function testDiffToMove()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $manipulator = new Manipulator($game);
        $forsyth = new Forsyth();
        $this->assertEquals(null, $forsyth->diffToMove($game, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq'));
        $this->assertEquals('e2 e4', $forsyth->diffToMove($game, 'rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq'));
    }
}
