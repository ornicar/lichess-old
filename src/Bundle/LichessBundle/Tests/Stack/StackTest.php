<?php

namespace Bundle\LichessBundle\Tests\Stack;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Stack;

require_once __DIR__.'/../gameBootstrap.php';

class StackTest extends \PHPUnit_Framework_TestCase
{
    protected $game;

    public function testMove()
    {
        $this->createGame($this->getData());
        $stack = new Stack();
        $manipulator = new Manipulator($this->game->getBoard(), $stack);
        $this->assertEquals(array(), $stack->getEvents());
        $manipulator->play('a2 a4');
        $this->assertEquals(array(array('type' => 'move', 'from' => 'a2', 'to' => 'a4')), $stack->getEvents());
    }

    public function testKill()
    {
        $this->createGame($this->getData());
        $stack = new Stack();
        $manipulator = new Manipulator($this->game->getBoard(), $stack);
        $manipulator->play('c3 e4');
        $this->assertEquals(array(array('type' => 'move', 'from' => 'c3', 'to' => 'e4')), $stack->getEvents());
    }

    public function testEnPassant()
    {
        $data = <<<EOF
        
        
    Pp  
        
        
        
        
k  K    
EOF;
        $game = $this->createGame($data);
        $game->setTurns(30);
        $wp = $game->getBoard()->getPieceByKey('e6');
        $bp = $game->getBoard()->getPieceByKey('f6');
        $wp->setFirstMove(12);
        $bp->setFirstMove(29);
        $stack = new Stack();
        $manipulator = new Manipulator($this->game->getBoard(), $stack);
        $manipulator->play('e6 f7');
        $this->assertEquals(array(
            array('type' => 'move', 'from' => 'e6', 'to' => 'f7'),
            array('type' => 'enpassant', 'killed' => 'f6')
        ), $stack->getEvents());
    }

    public function testCastling()
    {
        $data = <<<EOF
r  q rk 
pp  ppbp
 np  np 
  Q   B 
   pp b 
  N  N  
PP   PPP
R   K  R
EOF;
        $game = $this->createGame($data);
        $stack = new Stack();
        $manipulator = new Manipulator($this->game->getBoard(), $stack);
        $manipulator->play('e1 c1');
        $this->assertEquals(array(
            array('type' => 'move', 'from' => 'e1', 'to' => 'c1'),
            array('type' => 'castling', 'from' => 'a1', 'to' => 'd1')
        ), $stack->getEvents());
    }

    public function testPromotionQueen()
    {
        $data = <<<EOF
        
 P     k
        
        
        
        
        
K       
EOF;
        $game = $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('b7')->setFirstMove(1);
        $stack = new Stack();
        $manipulator = new Manipulator($this->game->getBoard(), $stack);
        $manipulator->play('b7 b8', array('promotion' => 'Queen'));
        $this->assertEquals(array(
            array('type' => 'move', 'from' => 'b7', 'to' => 'b8'),
            array('type' => 'promotion', 'class' => 'queen')
        ), $stack->getEvents());
    }

    public function testPromotionKnight()
    {
        $data = <<<EOF
        
 P      
   k    
        
        
        
        
K       
EOF;
        $game = $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('b7')->setFirstMove(1);
        $stack = new Stack();
        $manipulator = new Manipulator($this->game->getBoard(), $stack);
        $manipulator->play('b7 b8', array('promotion' => 'Knight'));
        $this->assertEquals(array(
            array('type' => 'move', 'from' => 'b7', 'to' => 'b8'),
            array('type' => 'promotion', 'class' => 'knight')
        ), $stack->getEvents());
    }

    public function testCheck()
    {
        $data = <<<EOF
        
 Q      
   k    
        
        
        
        
K       
EOF;
        $game = $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('b7')->setFirstMove(1);
        $stack = new Stack();
        $manipulator = new Manipulator($this->game->getBoard(), $stack);
        $manipulator->play('b7 b6', array('promotion' => 'Knight'));
        $this->assertEquals(array(
            array('type' => 'move', 'from' => 'b7', 'to' => 'b6'),
            array('type' => 'check', 'key' => 'd6')
        ), $stack->getEvents());
    }

    protected function getData()
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
        return $data;
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
