<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Entities\Game;

abstract class CastlingAbstractTest extends \PHPUnit_Framework_TestCase
{
    protected $game;
    protected $analyzer;

    abstract public function getVariant();

    /**
     * Get a game from visual data block
     *
     * @return Game
     **/
    protected function createGame($data, $blackTurn = false)
    {
        $generator = new Generator();
        $this->game = $generator->createGameFromVisualBlock($data);
        $this->game->setVariant($this->getVariant());
        $this->game->setStatus(Game::STARTED);
        $this->game->setTurns($blackTurn ? 11 : 10);
        $this->analyser = new Analyser($this->game->getBoard());
    }

    protected function assertMoves($pieceKey, $moves)
    {
        $moves = empty($moves) ? null : explode(' ', $moves);
        $possibleMoves = $this->analyser->getPlayerPossibleMoves($this->game->getTurnPlayer());
        $this->assertEquals($this->sort($moves), isset($possibleMoves[$pieceKey]) ? $this->sort($possibleMoves[$pieceKey]) : null);
    }

    protected function assertCanCastleKingSide($bool)
    {
        $player = $this->game->getTurnPlayer();
        $canCastleKingSide = $this->analyser->canCastleKingSide($player);
        $this->assertEquals($bool, $canCastleKingSide);
    }

    protected function assertCanCastleQueenSide($bool)
    {
        $player = $this->game->getTurnPlayer();
        $canCastleQueenSide = $this->analyser->canCastleQueenSide($player);
        $this->assertEquals($bool, $canCastleQueenSide);
    }

    protected function sort($array)
    {
        if(is_array($array)) {
            sort($array);
        }
        return $array;
    }
}
