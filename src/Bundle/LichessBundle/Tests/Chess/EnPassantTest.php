<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
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
        $game = $this->game = $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('a5')->setFirstMove($this->game->getTurns()-1);
        $this->move('b5 a6');
        $this->assertTrue($this->game->getBoard()->getPieceByKey('a6')->isClass('Pawn'));
        $this->assertTrue($this->game->getBoard()->getSquareByKey('b5')->isEmpty());
        $this->assertTrue($this->game->getBoard()->getSquareByKey('a5')->isEmpty());
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
    protected function createGame($data = null)
    {
        $generator = new Generator();
        if ($data) {
            $game = $generator->createGameFromVisualBlock($data);
        }
        else {
            $game = $generator->createGame();
        }
        $game->setIsStarted(true);
        $game->setTurns(30);
        return $game; 
    }
}
