<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\MoveFilter;

require_once __DIR__.'/../gameBootstrap.php';
require_once __DIR__.'/../../Chess/Manipulator.php';

class MoveFilterTest extends \PHPUnit_Framework_TestCase
{

    public function testProtectKingFilterNoSuicide()
    {
        $data = <<<EOF
rnbqkp r
pppppppp
        
        
        
    P n 
PPPP   P
RNBQK  R
EOF;

        $generator = new Generator();
        $game = $generator->createGameFromVisualBlock($data);
        $board = $game->getBoard();
        $piece = $board->getPieceByKey('e1');

        $moves = $this->createMoves($board, array('e2', 'f1', 'f2'));
        $expectedMoves = $this->createMoves($board, array('f2'));
        $filteredMoves = MoveFilter::filterProtectKing($piece, $moves);
        $this->assertEquals($board->squaresToKeys($expectedMoves), $board->squaresToKeys($filteredMoves));
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

        $generator = new Generator();
        $game = $generator->createGameFromVisualBlock($data);
        $board = $game->getBoard();
        
        $piece = $board->getPieceByKey('d1');
        $moves = $this->createMoves($board, array('e2', 'f3', 'g4'));
        $expectedMoves = $this->createMoves($board, array('e2'));
        $filteredMoves = MoveFilter::filterProtectKing($piece, $moves);
        $this->assertEquals($board->squaresToKeys($expectedMoves), $board->squaresToKeys($filteredMoves));
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

        $generator = new Generator();
        $game = $generator->createGameFromVisualBlock($data);
        $board = $game->getBoard();
        
        $piece = $board->getPieceByKey('d3');
        $moves = $this->createMoves($board, array('c4', 'b5', 'a6', 'e4', 'e2', 'f1'));
        $expectedMoves = $this->createMoves($board, array('e4', 'e2'));
        $filteredMoves = MoveFilter::filterProtectKing($piece, $moves);
        $this->assertEquals($board->squaresToKeys($expectedMoves), $board->squaresToKeys($filteredMoves));
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

        $generator = new Generator();
        $game = $generator->createGameFromVisualBlock($data);
        $board = $game->getBoard();
        
        $piece = $board->getPieceByKey('a2');
        $moves = $this->createMoves($board, array('a3', 'a4'));
        $expectedMoves = $this->createMoves($board, array());
        $filteredMoves = MoveFilter::filterProtectKing($piece, $moves);
        $this->assertEquals($board->squaresToKeys($expectedMoves), $board->squaresToKeys($filteredMoves));
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

        $generator = new Generator();
        $game = $generator->createGameFromVisualBlock($data);
        $board = $game->getBoard();
        
        $expectedMoveTree = array('d2' => array('e3'));
        $moveTree = $game->getPlayer('white')->getPossibleMoves();
        $moveTree = array_filter($moveTree);
        $this->assertEquals($expectedMoveTree, $moveTree);
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

        $generator = new Generator();
        $game = $generator->createGameFromVisualBlock($data);
        $board = $game->getBoard();
        $piece = $board->getPieceByKey('d2');

        $moves = $this->createMoves($board, array('d3', 'd4'));
        $expectedMoves = $this->createMoves($board, array());
        $filteredMoves = MoveFilter::filterProtectKing($piece, $moves);
        $this->assertEquals($board->squaresToKeys($expectedMoves), $board->squaresToKeys($filteredMoves));
    }

    public function testProtectKingFilterCanEatAttacker()
    {
        $data = <<<EOF
rnb k nr
pppppppp
        
        
        
        
PPPq  PP
RNBQK NR
EOF;

        $generator = new Generator();
        $game = $generator->createGameFromVisualBlock($data);
        $board = $game->getBoard();
        $piece = $board->getPieceByKey('e1');

        $moves = $this->createMoves($board, array('d2', 'e2', 'f2', 'f1'));
        $expectedMoves = $this->createMoves($board, array('d2', 'f1'));
        $filteredMoves = MoveFilter::filterProtectKing($piece, $moves);
        $this->assertEquals($board->squaresToKeys($expectedMoves), $board->squaresToKeys($filteredMoves));
    }

    protected function createMoves($board, array $toKeys)
    {
        $moves = array();
        foreach($toKeys as $toKey) {
            $moves[] = $board->getSquareByKey($toKey);
        }
        return $moves;
    }
}
