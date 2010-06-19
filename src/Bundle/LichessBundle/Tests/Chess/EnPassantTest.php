<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\PieceFilter;

require_once __DIR__.'/../gameBootstrap.php';
require_once __DIR__.'/../../Chess/Manipulator.php';

class EnPassantTest extends \PHPUnit_Framework_TestCase
{
    protected $game;

    public function testEnPassant()
    {
        $data = <<<EOF
       k
        
        
pP      
        
        
        
K       
EOF;
        $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('a5')->setFirstMove($this->game->getTurns()-1);
        $this->game->getBoard()->getPieceByKey('b5')->setFirstMove(0);
        $this->assertMoves('b5', 'b6 a6');
        $this->move('b5 a6');
        $this->assertTrue($this->game->getBoard()->getPieceByKey('a6')->isClass('Pawn'));
        $this->assertTrue($this->game->getBoard()->getSquareByKey('b5')->isEmpty());
        $this->assertTrue($this->game->getBoard()->getSquareByKey('a5')->isEmpty());
    }

    public function testEnPassantImpossibleWhenOneSquareOnly()
    {
        $data = <<<EOF
       k
        
pP      
        
        
        
        
K       
EOF;
        $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('a6')->setFirstMove($this->game->getTurns()-1);
        $this->game->getBoard()->getPieceByKey('b6')->setFirstMove(0);
        $this->assertMoves('b6', 'b7');
    }

    /**
     * Moves a piece and increment game turns
     *
     * @return void
     **/
    protected function move($move, array $options = array())
    {
        $manipulator = new Manipulator($this->game->getBoard());
        $manipulator->move($move, $options);
        $this->game->getBoard()->compile();
        $this->game->addTurn();
    }

    /**
     * Get a game from visual data block
     *
     * @return Game
     **/
    protected function createGame($data, $blackTurn = false)
    {
        $generator = new Generator();
        $this->game = $generator->createGameFromVisualBlock($data);
        $this->game->setIsStarted(true);
        $this->game->setTurns($blackTurn ? 11 : 10);
        $this->analyser = new Analyser($this->game->getBoard());
    }

    protected function assertMoves($pieceKey, $moves)
    {
        $moves = explode(' ', $moves);
        $possibleMoves = $this->analyser->getPlayerPossibleMoves($this->game->getTurnPlayer());
        $this->assertEquals($this->sort($moves), isset($possibleMoves[$pieceKey]) ? $this->sort($possibleMoves[$pieceKey]) : null);
    }

    protected function sort($array)
    {
        sort($array);
        return $array;
    }
}
