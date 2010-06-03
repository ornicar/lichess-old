<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Entities\Player;
use Bundle\LichessBundle\Entities\Piece as Piece;

class Generator
{
    /**
     * @return Game
     */
    public function createGame()
    {
        $game = new Game();

        $game->setPlayers(array(
            $this->createPlayer($game, 'white'),
            $this->createPlayer($game, 'black')
        ));

        return $game;
    }

    /**
     * @return Player
     */
    public function createPlayer($game, $color)
    {
        $player = new Player($color);
        $player->setGame($game);
        $player->setPieces($this->createPieces($player));

        return $player;
    }

    public function createPieces($player)
    {
        $pieces = array();

        foreach(explode(' ', 'Rook Knight Bishop Queen King Bishop Knight Rook') as $x => $class)
        {
            $this->createPiece('Pawn', $player, $x+1);
            $this->createPiece($class, $player, $x+1);
        }

        return $pieces;
    }

    /**
     * @return Piece
     */
    public function createPiece($class, $player, $x)
    {
        $class = 'Bundle\\LichessBundle\\Entities\\Piece\\'.$class;

        if('white' === $player->getColor()) {
            $y = 'Pawn' === $class ? 1 : 2;
        } else {
            $y = 'Pawn' === $class ? 8 : 7;
        }

        $piece = new $class($x, $y);
        $piece->setPlayer($player);

        return $piece;
    }
}
