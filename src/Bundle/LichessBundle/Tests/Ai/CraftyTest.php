<?php

namespace Bundle\LichessBundle\Tests\Ai;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Ai\Crafty;

class CraftyTest extends \PHPUnit_Framework_TestCase
{
    protected $board;
    protected $game;
    protected $manipulator;

    public function setup()
    {
        $generator = new Generator();
        $this->game = $generator->createGame();
        $this->board = $this->game->getBoard();
        $this->manipulator = new Manipulator($this->game);
    }

    public function testMoveFormat()
    {
        $ai = new Crafty();
        $move = $ai->move($this->game, 1);
        $this->assertRegexp('/[a-h][1-8]\s[a-h][1-8]/', $move);
    }

    public function testMoveValid()
    {
        $dump = $this->board->dump();
        $ai = new Crafty();
        $move = $ai->move($this->game, 1);
        $this->manipulator->play($move);
        $this->assertNotEquals($dump, $this->board->dump());
    }

    public function testMoveManyTimes()
    {
        for($it=0; $it<8; $it++) {
            $dump = $this->board->dump();
            $ai = new Crafty();
            $move = $ai->move($this->game, 1);
            $this->manipulator->play($move);
            $this->assertNotEquals($dump, $this->board->dump());
        }
    }

    public function testLevels()
    {
        $ai = new Crafty();
        for($level=1; $level<=8; $level++) {
            $move = $ai->move($this->game, $level);
            $this->assertRegexp('/[a-h][1-8]\s[a-h][1-8]/', $move);
        }
    }

}
