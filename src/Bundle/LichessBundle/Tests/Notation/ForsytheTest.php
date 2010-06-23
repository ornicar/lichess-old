<?php

namespace Bundle\LichessBundle\Tests\Notation;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Notation\Forsythe;

class ForsytheTest extends \PHPUnit_Framework_TestCase
{
    public function testExport()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $manipulator = new Manipulator($game->getBoard());
        $forsythe = new Forsythe();
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq', $forsythe->export($game));
        $manipulator->play('e2 e4');
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq', $forsythe->export($game));
        $manipulator->play('c7 c5');
        $this->assertEquals('rnbqkbnr/pp1ppppp/8/2p5/4P3/8/PPPP1PPP/RNBQKBNR w KQkq', $forsythe->export($game));
        $manipulator->play('g1 f3');
        $this->assertEquals('rnbqkbnr/pp1ppppp/8/2p5/4P3/5N2/PPPP1PPP/RNBQKB1R b KQkq', $forsythe->export($game));
    }
    
    public function testExportCastling()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $game->getBoard()->getPieceByKey('a1')->setFirstMove(1);
        $game->getBoard()->getPieceByKey('h8')->setFirstMove(1);
        $manipulator = new Manipulator($game->getBoard());
        $forsythe = new Forsythe();
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w Kq', $forsythe->export($game));
    }
    
    public function testDiffToMove()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $manipulator = new Manipulator($game->getBoard());
        $forsythe = new Forsythe();
        $this->assertEquals(null, $forsythe->diffToMove($game, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq'));
        $this->assertEquals('e2 e4', $forsythe->diffToMove($game, 'rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq'));
    }
}
