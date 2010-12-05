<?php

namespace Bundle\LichessBundle\Tests\Ai;

use Bundle\LichessBundle\Tests\ChessTest;
use Bundle\LichessBundle\Ai\Stupid;

class StupidTest extends ChessTest
{
    protected $game;
    protected $board;
    protected $manipulator;
    protected $ai;

    public function setup()
    {
        $generator = $this->getGenerator();
        $this->game = $generator->createGame();
        $this->board = $this->game->getBoard();
        $this->manipulator = $this->getManipulator($this->game);
        $this->ai = new Stupid();
    }

    public function testCreateAi()
    {
        $this->assertTrue($this->ai instanceof Stupid);
    }

    public function testMoveFormat()
    {
        $move = $this->ai->move($this->game, 1);
        $this->assertRegexp('/[a-h][1-8]\s[a-h][1-8]/', $move);
    }

    public function testMoveValid()
    {
        $dump = $this->board->dump();
        $move = $this->ai->move($this->game, 1);
        $this->manipulator->move($move);
        $this->board->compile();
        $this->assertNotEquals($dump, $this->board->dump());
    }

    public function testMoveManyTimes()
    {
        for($it=0; $it<5; $it++) {
            $dump = $this->board->dump();
            $move = $this->ai->move($this->game, 1);
            $this->manipulator->move($move);
            $this->board->compile();
            $this->assertNotEquals($dump, $this->board->dump());
        }
    }

}
