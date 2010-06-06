<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\MoveFilter;

require_once __DIR__.'/../gameBootstrap.php';
require_once __DIR__.'/../../Chess/Manipulator.php';

class PossibleMovesTest extends \PHPUnit_Framework_TestCase
{
    public function test1()
    {
        $data = <<<EOF
 nbqkp r
pppppppp
        
        
        
    rr  
PPPP  PP
RNBPKPRR
EOF;
        $expectedMoveTree = array('d2' => array('e3'));
        $moveTree = $this->computePossibleMoves($data, 'white');
        $this->assertEquals($expectedMoveTree, $moveTree);
    }

    public function test2()
    {
        $data = <<<EOF
       k
        
        
        
        
        
        
K       
EOF;
        $expectedMoveTree = array('a1' => array('a2', 'b1', 'b2'));
        $moveTree = $this->computePossibleMoves($data);
        $this->assertEquals($expectedMoveTree, $moveTree);

        $expectedMoveTree = array('h8' => array('h7', 'g7', 'g8'));
        $moveTree = $this->computePossibleMoves($data, 1);
        $this->assertEquals($expectedMoveTree, $moveTree);
    }

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
        $moveTree = $this->computePossibleMoves($data);
        $this->assertEquals(array('e2', 'd1', 'd2', 'c1'), $moveTree['e1']);
        $moveTree = $this->computePossibleMoves($data, 1);
        $this->assertEquals(array('h8'), $moveTree['g8']);
        $this->assertEquals(array('d3', 'd2', 'c3'), $moveTree['d4']);
    }

    public function test4()
    {
        $data = <<<EOF
r    rk 
pp  p bp
 np  np 
   Q  B 
   pp b 
 q   N  
PPP  PPP
R  K B R
EOF;
        $moveTree = $this->computePossibleMoves($data, 1);
        $this->assertEquals(array('h8'), $moveTree['g8']);
        $this->assertEquals(array('d5'), $moveTree['f6']);
        $this->assertFalse(isset($moveTree['a7']));
    }

    protected function computePossibleMoves($data, $turn = 0)
    {
        $generator = new Generator();
        $game = $generator->createGameFromVisualBlock($data);
        $game->setTurns($turn);
        
        $moveTree = $game->getTurnPlayer()->getPossibleMoves();
        $moveTree = array_filter($moveTree);
        return $moveTree;
    }
}
