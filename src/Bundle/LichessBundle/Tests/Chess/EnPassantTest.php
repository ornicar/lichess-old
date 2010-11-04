<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\PieceFilter;
use Bundle\LichessBundle\Entities\Game;

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

    public function testEnPassantCanSaveTheKing()
    {
        $data = <<<EOF
    r  k

   p
  pP b
  PK

  r

EOF;
        $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('c5')->setFirstMove($this->game->getTurns()-1);
        // King can not move
        $this->assertMoves('d4', '');
        // En passant is possible
        $this->assertMoves('d5', 'c6');
    }

    /**
     * Moves a piece and increment game turns
     *
     * @return void
     **/
    protected function move($move, array $options = array())
    {
        $manipulator = new Manipulator($this->game);
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
        $this->game->setStatus(Game::STARTED);
        $this->game->setTurns($blackTurn ? 11 : 10);
        $this->analyser = new Analyser($this->game->getBoard());
    }

    protected function assertMoves($pieceKey, $moves)
    {
        $moves = empty($moves) ? null : $this->sort(explode(' ', $moves));
        $possibleMoves = $this->analyser->getPlayerPossibleMoves($this->game->getTurnPlayer());
        $this->assertEquals($moves, isset($possibleMoves[$pieceKey]) ? $this->sort($possibleMoves[$pieceKey]) : null);

        //$piecePossibleMoves = $this->analyser->getPiecePossibleMoves($this->game->getBoard()->getPieceByKey($pieceKey));
        //$this->assertEquals($moves, $this->sort($piecePossibleMoves));
    }

    protected function sort($array)
    {
        sort($array);
        return $array;
    }
}
