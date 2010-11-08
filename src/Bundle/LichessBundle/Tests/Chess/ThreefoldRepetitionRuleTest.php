<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\PieceFilter;
use Bundle\LichessBundle\Document\Game;

class ThreefoldRepetitionRule extends \PHPUnit_Framework_TestCase
{
    protected $game;

    public function testNewGameIsNotThreefold()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $this->assertFalse($game->isThreefoldRepetition());
    }

    public function testPlayedGameIsNotThreefold()
    {
        $game = $this->game = $this->createGame();

        $this->applyMoves(array(
            'e2 e4',
            'c7 c5',
            'c2 c3',
            'd7 d5',
            'e4 d5'
        ));
        $this->assertFalse($game->isThreefoldRepetition());
    }

    public function testTwofoldIsNotThreefold()
    {
        $game = $this->game = $this->createGame();

        $this->applyMoves(array(
            'b1 c3',
            'b8 c6',
            'c3 b1',
            'c6 b8',
            'b1 c3',
            'b8 c6',
            'c3 b1',
            'c6 b8',
        ));
        $this->assertFalse($game->isThreefoldRepetition());
    }

    public function testRepeatedGameIsThreefold()
    {
        $game = $this->game = $this->createGame();

        $this->applyMoves(array(
            'b1 c3',
            'b8 c6',
            'c3 b1',
            'c6 b8',
            'b1 c3',
            'b8 c6',
            'c3 b1',
            'c6 b8',
            'b1 c3',
            'b8 c6',
            'c3 b1',
            'c6 b8',
        ));
        $this->assertTrue($game->isThreefoldRepetition());
    }

    /**
     * apply moves
     **/
    protected function applyMoves(array $moves)
    {
        foreach ($moves as $move)
        {
            $this->move($move);
        }
    }

    /**
     * Moves a piece and increment game turns
     *
     * @return void
     **/
    protected function move($move, array $options = array())
    {
        $manipulator = new Manipulator($this->game);
        $manipulator->play($move, $options);
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
            $game->setTurns(20);
        }
        else {
            $game = $generator->createGame();
        }
        $game->setStatus(Game::STARTED);
        return $game;
    }
}

