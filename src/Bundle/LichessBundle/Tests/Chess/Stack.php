<?php

namespace Bundle\LichessBundle\Tests\Stack;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Stack;

require_once __DIR__.'/../gameBootstrap.php';

class StackTest extends \PHPUnit_Framework_TestCase
{
    protected $game;

    public function test3()
    {
        $data = <<<EOF
r  q rk 
pp  ppbp
 np  np 
  Q   B 
   pp b 
  N  N  
PP   PPP
R   KB R
EOF;
        $stack = new Stack();
        $manipulator = new Manipulator($this->game->getBoard(), $stack);
        $this->assertEquals(array(), $stack->getEvents());
        $manipulator->play('a2 a4');
        $this->assertEquals(array(array('type' => 'move', 'from' => 'a2', 'to' => 'a4')));
    }

    /**
     * Get a game from visual data block
     *
     * @return Game
     **/
    protected function createGame($data)
    {
        $generator = new Generator();
        $this->game = $generator->createGameFromVisualBlock($data);
        return $this->game; 
    }
}
