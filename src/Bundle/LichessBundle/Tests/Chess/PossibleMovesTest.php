<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Entities\Game;

require_once __DIR__.'/../gameBootstrap.php';
require_once __DIR__.'/../../Chess/Manipulator.php';

class PossibleMovesTest extends \PHPUnit_Framework_TestCase
{
    protected $game;

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
        $this->assertEquals($expectedMoveTree, array_filter($moveTree));
    }

    public function test2()
    {
        $data = <<<EOF
       k
        
        
        
        
        
        
K       
EOF;
        $expectedMoveTree = array('a1' => array('a2', 'b1', 'b2'));
        $moveTree = $this->computePossibleMoves($data);
        $this->assertEquals($expectedMoveTree, array_filter($moveTree));

        $expectedMoveTree = array('h8' => array('g7', 'g8', 'h7'));
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
        $this->assertEquals(array('d1', 'd2', 'e2', 'c1'), $moveTree['e1']);
        $this->assertEquals($this->sort(array('a5', 'b5', 'd5', 'e5', 'f5', 'c4', 'c6', 'b6', 'd6', 'e7', 'd4', 'a3', 'b4')), $this->sort($moveTree['c5']));
        $this->assertEquals($this->sort(array('f6', 'h6', 'h4', 'f4', 'e3', 'd2', 'c1')), $this->sort($moveTree['g5']));
        $this->assertFalse(isset($moveTree['f2']));
        $moveTree = $this->computePossibleMoves($data, 1);
        $this->assertEquals(array('d3', 'd2', 'c3'), $moveTree['d4']);
        $this->assertEquals($this->sort(array('f5', 'e6', 'd7', 'c8', 'h5', 'h3', 'f3')), $this->sort($moveTree['g4']));
    }

    public function testKings()
    {
        $data = <<<EOF
        
        
        
        
        
        
  k     
K       
EOF;
        $expectedMoveTree = array('a1' => array('a2'));
        $moveTree = $this->computePossibleMoves($data);
        $this->assertEquals($expectedMoveTree, array_filter($moveTree));
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
        $moveTree = $this->computePossibleMoves($data);
        $this->assertEquals(array('d1', 'd2', 'e2', 'f1', 'c1', 'g1'), $moveTree['e1']);
    }

    public function testCastlingImpossibleDueToRemainingPiece()
    {
        $data = <<<EOF
r  q rk 
pp  ppbp
 np  np 
  Q   B 
   pp   
        
PP   PPP
RN  K NR
EOF;
        $moveTree = $this->computePossibleMoves($data);
        $this->assertEquals(array('d1', 'd2', 'e2', 'f1'), $moveTree['e1']);
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

    public function testEnPassant()
    {
        $data = <<<EOF
        
        
        
    Pp  
        
        
        
k  K    
EOF;
        $game = $this->createGame($data);
        $game->setTurns(30);
        $wp = $game->getBoard()->getPieceByKey('e5');
        $bp = $game->getBoard()->getPieceByKey('f5');

        $wp->setFirstMove(12);
        $bp->setFirstMove(29);
        $analyser = new Analyser($game->getBoard());
        $possibleMoves = $analyser->getPlayerPossibleMoves($game->getTurnPlayer());
        $this->assertEquals(array('e6', 'f6'), $possibleMoves['e5']);

        $wp->setFirstMove(12);
        $bp->setFirstMove(13);
        $possibleMoves = $analyser->getPlayerPossibleMoves($game->getTurnPlayer());
        $this->assertEquals(array('e6'), $possibleMoves['e5']);
    }

    protected function computePossibleMoves($data, $turn = 0)
    {
        $game = $this->createGame($data);
        $game->setTurns($turn);
        $game->setStatus(Game::STARTED);
        
        $analyser = new Analyser($game->getBoard());
        $moveTree = $analyser->getPlayerPossibleMoves($game->getTurnPlayer());
        return $moveTree;
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

    protected function sort($array)
    {
        sort($array);
        return $array;
    }
}
