<?php

namespace Bundle\LichessBundle\Tests\Ai;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Ai\Stupid;

require_once __DIR__.'/../gameBootstrap.php';
require_once __DIR__.'/../../Ai.php';
require_once __DIR__.'/../../Ai/Stupid.php';
require_once __DIR__.'/../../Chess/Manipulator.php';

class StupidTest extends \PHPUnit_Framework_TestCase
{
    protected $game;
    protected $board;
    protected $manipulator;
    protected $ai;

    public function setup()
    {
        $generator = new Generator();
        $this->game = $generator->createGame();
        $this->board = $this->game->getBoard();
        $this->manipulator = new Manipulator($this->board);
        $this->ai = new Stupid(1);
    }

    public function testCreateAi()
    {
        $this->assertTrue($this->ai instanceof Stupid);
    }

    public function testMoveFormat()
    {
        $move = $this->ai->move($this->game);
        $this->assertRegexp('/[a-h][1-8]\s[a-h][1-8]/', $move);
    }

    public function testMoveValid()
    {
        $dump = $this->board->dump();
        $move = $this->ai->move($this->game);
        $this->manipulator->move($move);
        $this->board->compile();
        $this->assertNotEquals($dump, $this->board->dump());
    }

    public function testMoveManyTimes()
    {
        for($it=0; $it<5; $it++) {
            $dump = $this->board->dump();
            $move = $this->ai->move($this->game);
            $this->manipulator->move($move);
            $this->board->compile();
            $this->assertNotEquals($dump, $this->board->dump());
        }
    }

}
