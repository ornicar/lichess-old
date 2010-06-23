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
            'white' => $this->createPlayer($game, 'white'),
            'black' => $this->createPlayer($game, 'black')
        ));

        $game->setCreator($game->getPlayer('white'));

        return $game;
    }

    /**
     * Creates a return game for the given player,
     * reverting players colors
     *
     * @param  Player the player who creates the return game
     * @return Player the new player on the new game
     **/
    public function createReturnGame(Player $player)
    {
        $nextGame = $this->createGame();
        $nextPlayer = $nextGame->getPlayer($player->getOpponent()->getColor());
        $nextGame->setCreator($nextPlayer);
        $player->getGame()->setNext($nextGame->getHash());

        return $nextPlayer;
    }

    public function createGameForPlayer($color)
    {
        $game = $this->createGame();
        $player = $game->getPlayer($color);
        $game->setCreator($player);
        return $player;
    }

    /**
     * Create a game from a visual block notation like:
r bqkb r
 ppp ppp
p n  n  
    p   
B   P   
     N  
PPPP PPP
RNBQK  R
    */
    public function createGameFromVisualBlock($data)
    {
        $game = new Game();

        $players = array();
        foreach(array('white', 'black') as $color) {
            $player = new Player($color);
            $player->setGame($game);
            $players[$color] = $player;
        }

        $game->setPlayers($players);
        $game->setCreator($game->getPlayer('white'));

        foreach(explode("\n", $data) as $_y => $line) {
            $y = 8-$_y;
            for($x=1; $x<9; $x++) {
                $byte = $line{$x-1};
                if(' ' === $byte) {
                    continue;
                }
                $color = ctype_lower($byte) ? 'black' : 'white';
                $player = $game->getPlayer($color);
                switch(strtolower($byte)) {
                    case 'p': $class = 'Pawn'; break;
                    case 'r': $class = 'Rook'; break;
                    case 'n': $class = 'Knight'; break;
                    case 'b': $class = 'Bishop'; break;
                    case 'q': $class = 'Queen'; break;
                    case 'k': $class = 'King'; break;
                }
                $fullClass = 'Bundle\\LichessBundle\\Entities\\Piece\\'.$class;
                $piece = new $fullClass($x, $y);
                $piece->setPlayer($player);
                $player->addPiece($piece);
            }
        }

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
            $pieces[] = $this->createPiece('Pawn', $player, $x+1);
            $pieces[] = $this->createPiece($class, $player, $x+1);
        }

        return $pieces;
    }

    /**
     * @return Piece
     */
    public function createPiece($class, $player, $x)
    {
        if('white' === $player->getColor()) {
            $y = 'Pawn' === $class ? 2 : 1;
        } else {
            $y = 'Pawn' === $class ? 7 : 8;
        }

        $fullClass = 'Bundle\\LichessBundle\\Entities\\Piece\\'.$class;

        $piece = new $fullClass($x, $y);
        $piece->setPlayer($player);

        return $piece;
    }
}
