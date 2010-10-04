<?php

namespace Bundle\LichessBundle\Tests\Notation;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Notation\PgnDumper;

class PgnDumperTest extends \PHPUnit_Framework_TestCase
{
    protected $game;
    protected $analyzer;

    public function testNewGame()
    {
        $this->createGame();

        $pgn = '[Site "http://lichess.org/"]
[Result "*"]

*';

        $this->assertGamePgn($pgn);
    }

    public function testDumpMoves()
    {
        $this->createGame();
        $this->game->setPgnMoves('e4 e5 Nf3 Nc6 Bb5 a6 Ba4 Nf6 O-O Be7 Re1 b5 Bb3 d6 c3 O-O h3 Nb8 d4 Nbd7');
        $dumper = new PgnDumper();
        $moves = $dumper->getPgnMoves($this->game);
        $expected = '1.e4 e5 2.Nf3 Nc6 3.Bb5 a6 4.Ba4 Nf6 5.O-O Be7 6.Re1 b5 7.Bb3 d6 8.c3 O-O 9.h3 Nb8 10.d4 Nbd7';
        $this->assertEquals($expected, $moves);
    }

    public function testDisambiguationFile()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp
        
        
    N   
        
PPPPPPPP
RNBQKB R
EOF;

        $this->createGame($data);
        $piece = $this->game->getBoard()->getPieceByKey('b1');
        $manipulator = new Manipulator($this->game);
        $manipulator->play('b1 c3');
        $this->assertEquals('Nbc3', $this->game->getPgnMoves());
    }

    public function testDisambiguationRank()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp
        
        
        
 N      
PPP PPPP
RNBQKB R
EOF;

        $this->createGame($data);
        $piece = $this->game->getBoard()->getPieceByKey('b1');
        $manipulator = new Manipulator($this->game);
        $manipulator->play('b1 d2');
        $this->assertEquals('N1d2', $this->game->getPgnMoves());
    }

    public function testDisambiguationFileAndRank()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp
        
        
        
 N      
PPP PPPP
RNBQKN R
EOF;

        $this->createGame($data);
        $manipulator = new Manipulator($this->game);
        $manipulator->play('b1 d2');
        $this->assertEquals('Nb1d2', $this->game->getPgnMoves());
    }

    public function testEnPassant()
    {
        $data = <<<EOF
rnbqkbnr
pppppp p
        
     Pp 
        
 N      
PPP PPPP
RNBQKN R
EOF;

        $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('g5')->setFirstMove(9);
        $manipulator = new Manipulator($this->game);
        $manipulator->play('f5 g6');
        $this->assertEquals('fxg6', $this->game->getPgnMoves());
    }

    public function testPromotionKnight()
    {
        $data = <<<EOF
rnbqk   
ppppp P 
        
     Pp 
        
 N      
PPP PPPP
RNBQKN R
EOF;

        $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('g7')->setFirstMove(2);
        $manipulator = new Manipulator($this->game);
        $manipulator->play('g7 g8', array('promotion' => 'Knight'));
        $this->assertEquals('g8=N', $this->game->getPgnMoves());
    }

    public function testPromotionQueenWithCheckMate()
    {
        $data = <<<EOF
rnbqk   
ppppp P 
        
     Pp 
        
 N      
PPP PPPP
RNBQKN R
EOF;

        $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('g7')->setFirstMove(2);
        $manipulator = new Manipulator($this->game);
        $manipulator->play('g7 g8', array('promotion' => 'Queen'));
        $this->assertEquals('g8=Q#', $this->game->getPgnMoves());
    }

    public function testGioachinoGreco()
    {
        $this->createGame();
        $this->applyMoves(array('d2 d4', 'd7 d5', 'c2 c4', 'd5 c4', 'e2 e3', 'b7 b5', 'a2 a4', 'c7 c6', 'a4 b5', 'c6 b5', 'd1 f3'));

        $this->game->setStatus(Game::RESIGN);
        $this->game->getPlayer('white')->setIsWinner(true);

        $pgn = <<<EOF
[Site "http://lichess.org/"]
[Result "1-0"]

1.d4 d5 2.c4 dxc4 3.e3 b5 4.a4 c6 5.axb5 cxb5 6.Qf3 1-0
EOF;

        $this->assertGamePgn($pgn);
    }

    public function testPeruvianImmortal()
    {
        $this->createGame();

        $this->applyMoves(array(
            'e2 e4',
            'd7 d5',
            'e4 d5',
            'd8 d5',
            'b1 c3',
            'd5 a5',
            'd2 d4',
            'c7 c6',
            'g1 f3',
            'c8 g4',
            'c1 f4',
            'e7 e6',
            'h2 h3',
            'g4 f3',
            'd1 f3',
            'f8 b4',
            'f1 e2',
            'b8 d7',
            'a2 a3',
            'e8 c8',
            'a3 b4',
            'a5 a1',
            'e1 d2',
            'a1 h1',
            'f3 c6',
            'b7 c6',
            'e2 a6'
        ));

        $pgn = <<<EOF
[Site "http://lichess.org/"]
[Result "1-0"]

1.e4 d5 2.exd5 Qxd5 3.Nc3 Qa5 4.d4 c6 5.Nf3 Bg4 6.Bf4 e6 7.h3 Bxf3 8.Qxf3 Bb4 9.Be2 Nd7 10.a3 O-O-O 11.axb4 Qxa1+ 12.Kd2 Qxh1 13.Qxc6+ bxc6 14.Ba6# 1-0
EOF;

        $this->assertGamePgn($pgn);
    }

    protected function assertGamePgn($pgn)
    {
        $dumper = new PgnDumper();
        $dumped = $dumper->dumpGame($this->game);
        $pgn = str_replace('%date%', date('Y.m.d'), $pgn);
        $this->assertEquals($pgn, $dumped);
    }

    /**
     * Get a game from visual data block
     *
     * @return Game
     **/
    protected function createGame($data = null, $blackTurn = false)
    {
        $generator = new Generator();
        if($data) {
            $this->game = $generator->createGameFromVisualBlock($data);
        }
        else {
            $this->game = $generator->createGame();
        }
        $this->game->setStatus(Game::STARTED);
        $this->game->setTurns($blackTurn ? 11 : 10);
        $this->analyser = new Analyser($this->game->getBoard());
    }

    /**
     * Verify the game state
     *
     * @return void
     **/
    protected function assertDump($dump)
    {
        $dump = "\n".$dump."\n";
        $this->assertEquals($dump, $this->game->getBoard()->dump());
    }

    /**
     * apply moves
     **/
    protected function applyMoves(array $moves)
    {
        $manipulator = new Manipulator($this->game);
        foreach ($moves as $move)
        {
            $manipulator->play($move);
        }
    }
}
