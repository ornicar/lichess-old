<?php

namespace Bundle\LichessBundle\Tests\Ai;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Ai\Crafty;

require_once __DIR__.'/../gameBootstrap.php';
require_once __DIR__.'/../../Ai.php';
require_once __DIR__.'/../../Ai/Crafty.php';
require_once __DIR__.'/../../Notation/Forsythe.php';

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
        $this->manipulator = new Manipulator($this->board);
    }

    public function testMoveFormat()
    {
        $ai = new Crafty(1);
        $move = $ai->move($this->game);
        $this->assertRegexp('/[a-h][1-8]\s[a-h][1-8]/', $move);
    }

    public function testMoveValid()
    {
        $dump = $this->board->dump();
        $ai = new Crafty(1);
        $move = $ai->move($this->game);
        $this->manipulator->play($move);
        $this->assertNotEquals($dump, $this->board->dump());
    }

    public function testMoveManyTimes()
    {
        for($it=0; $it<8; $it++) {
            $dump = $this->board->dump();
            $ai = new Crafty(1);
            $move = $ai->move($this->game);
            $this->manipulator->play($move);
            $this->assertNotEquals($dump, $this->board->dump());
        }
    }

    public function testLevels()
    {
        for($level=1; $level<=8; $level++) {
            $ai = new Crafty($level);
            $move = $ai->move($this->game);
            $this->assertRegexp('/[a-h][1-8]\s[a-h][1-8]/', $move);
        }
    }

}
