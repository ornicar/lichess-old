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

        $pgn = <<<EOF
[Site "http://lichess.org/"]
[Date "%date%"]
[Result "*"]

*
EOF;

        $this->assertGamePgn($pgn);
    }

    //public function testPeruvianImmortal()
    //{
        //$this->createGame();

        //$this->applyMoves(array(
            //'e2 e4',
            //'d7 d5',
            //'e4 d5',
            //'d8 d5',
            //'b1 c3',
            //'d5 a5',
            //'d2 d4',
            //'c7 c6',
            //'g1 f3',
            //'c8 g4',
            //'c1 f4',
            //'e7 e6',
            //'h2 h3',
            //'g4 f3',
            //'d1 f3',
            //'f8 b4',
            //'f1 e2',
            //'b8 d7',
            //'a2 a3',
            //'e8 c8'
        //));

        //$pgn = <<<EOF
//[Site "http://lichess.org/"]
//[Date "%date%"]
//[Result "0-0"]

//1.e4 d5 2.exd5 Qxd5 3.Nc3 Qa5 4.d4 c6 5.Nf3 Bg4 6.Bf4 e6 7.h3
//Bxf3 8.Qxf3 Bb4 9.Be2 Nd7 10.a3 O-O-O 11.axb4 Qxa1+ 12.Kd2
//Qxh1 13.Qxc6+ bxc6 14.Ba6# 1-0
//EOF;

        //$this->assertPgn($pgn);
    //}

    protected function assertGamePgn($pgn)
    {
        $options = array();
        $dumper = new PgnDumper($options);
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
        $this->game->setUpdatedAt(time());        
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
