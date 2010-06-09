<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Analyser;

require_once __DIR__.'/../gameBootstrap.php';

class AnalyserTest extends \PHPUnit_Framework_TestCase
{
    protected $game;
    protected $board;
    protected $analyser;

    public function testProtectKingFilterNoSuicide()
    {
        $data = <<<EOF
rnbqkp r
pppppppp
        
        
        
    P n 
PPPP   P
RNBQK  R
EOF;

        $this->generate($data);
        $piece = $this->board->getPieceByKey('e1');
        $this->assertEquals(array('f2', 'g1'), $this->getPiecePossibleMoves($piece));
    }

    public function testProtectKingFilterMustMoveToDefend()
    {
        $data = <<<EOF
 nbqkp r
pppppppp
        
        
    r   
        
PPPP   P
RNBQK  R
EOF;
        $this->generate($data);
        $piece = $this->board->getPieceByKey('d1');
        $this->assertEquals(array('e2'), $this->getPiecePossibleMoves($piece));
    }

    public function testProtectKingFilterMustEatToDefend()
    {
        $data = <<<EOF
 nbqkp r
pppppppp
        
        
    r   
   B    
PPPP   P
R  QK  R
EOF;
        $this->generate($data);
        $piece = $this->board->getPieceByKey('d3');
        $this->assertEquals(array('e4', 'e2'), $this->getPiecePossibleMoves($piece));
    }

    public function testProtectKingFilterMustStayToDefend()
    {
        $data = <<<EOF
 nbqkp r
pppppppp
        
        
    r   
        
PPPP   P
RNBQK  R
EOF;
        $this->generate($data);
        $piece = $this->board->getPieceByKey('a2');
        $this->assertEquals(array(), $this->getPiecePossibleMoves($piece));
    }

    public function testProtectKingFilterMustEatToDefendAllPossibleMoves()
    {
        $data = <<<EOF
 nbqkp r
pppppppp
        
        
        
    rr  
PPPP  PP
RNBPKPRR
EOF;
        $this->generate($data);
        $piece = $this->board->getPieceByKey('d2');
        $this->assertEquals(array('e3'), $this->getPiecePossibleMoves($piece));
    }

    public function testProtectKingFilterDecouverte()
    {
        $data = <<<EOF
rnbqk nr
pppppppp
        
        
 b      
        
PPPPPPPP
RNBQKBNR
EOF;
        $this->generate($data);
        $piece = $this->board->getPieceByKey('d2');
        $this->assertEquals(array(), $this->getPiecePossibleMoves($piece));
    }

    public function testProtectKingFilterCanEatAttacker()
    {
        $data = <<<EOF
rnb k nr
pppppppp
        
        
        
        
PPPq  PP
RNBQK NR
EOF;
        $this->generate($data);
        $piece = $this->board->getPieceByKey('e1');
        $this->assertEquals(array('d2', 'f1'), $this->getPiecePossibleMoves($piece));
    }

    protected function getPiecePossibleMoves($piece)
    {
        $analysis = $this->analyser->getPlayerPossibleMoves($piece->getPlayer());
        $key = $piece->getSquareKey();
        return isset($analysis[$key]) ? $analysis[$key] : array();
    }

    protected function generate($data)
    {
        $generator = new Generator();
        $this->game = $generator->createGameFromVisualBlock($data);
        $this->board = $this->game->getBoard();
        $class = 'Bundle\\LichessBundle\\Chess\\Analyser';
        $this->analyser = new $class($this->board);
    }
}
