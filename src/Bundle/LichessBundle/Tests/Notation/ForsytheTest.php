<?php

namespace Bundle\LichessBundle\Tests\Notation;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Notation\Forsyth;

class ForsythTest extends \PHPUnit_Framework_TestCase
{
    public function testImportStandardFen()
    {
        $this->markTestSkipped();

        $forsyth = new Forsyth();
        $fen = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq';
        $game = new Game();
        $game = $forsyth->import($game, $fen);

        foreach($game->players as $color => $player) {
            foreach(explode(' ', 'Rook Knight Bishop Queen King Bishop Knight Rook') as $x => $class) {
                $pawn = $game->getBoard()->getPieceByPos($x+1, 'white' === $color ? 2 : 7);
                $this->assertNotNull($pawn);
                $this->assertEquals($pawn->getClass(), 'Pawn');
                $this->assertEquals($pawn->getPlayer(), $player);
                $piece = $game->getBoard()->getPieceByPos($x+1, 'white' === $color ? 1 : 8);
                $this->assertNotNull($piece);
                $this->assertEquals($piece->getClass(), $class);
                $this->assertEquals($piece->getPlayer(), $player);
            }
        }
    }

    public function testExport()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $manipulator = new Manipulator($game);
        $forsyth = new Forsyth();
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq', $forsyth->export($game));
        $manipulator->play('e2 e4');
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq', $forsyth->export($game));
        $manipulator->play('c7 c5');
        $this->assertEquals('rnbqkbnr/pp1ppppp/8/2p5/4P3/8/PPPP1PPP/RNBQKBNR w KQkq', $forsyth->export($game));
        $manipulator->play('g1 f3');
        $this->assertEquals('rnbqkbnr/pp1ppppp/8/2p5/4P3/5N2/PPPP1PPP/RNBQKB1R b KQkq', $forsyth->export($game));
    }

    public function testExportCastling()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $game->getBoard()->getPieceByKey('a1')->setFirstMove(1);
        $game->getBoard()->getPieceByKey('h8')->setFirstMove(1);
        $manipulator = new Manipulator($game);
        $forsyth = new Forsyth();
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w Kq', $forsyth->export($game));
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
