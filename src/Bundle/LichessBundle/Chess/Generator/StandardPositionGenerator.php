<?php

namespace Bundle\LichessBundle\Chess\Generator;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Piece;

class StandardPositionGenerator extends PositionGenerator
{
    public function createPieces(Game $game)
    {
        $pieces = array();
        $player = $game->getPlayer('white');

        foreach(explode(' ', 'Rook Knight Bishop Queen King Bishop Knight Rook') as $x => $class)
        {
            $pieces[] = new Piece($x+1, 2, 'Pawn');
            $pieces[] = new Piece($x+1, 1, $class);
        }

        $player->setPieces($pieces);
        $player->getOpponent()->setPieces($this->mirrorPieces($pieces));

        $game->setInitialFen(null);
    }

    public function createPiecesMinimal(Game $game)
    {
        $pieces = array();
        $player = $game->getPlayer('white');
        $pieces[] = new Piece(1, 2, 'Pawn');
        $player->setPieces($pieces);
        $player->getOpponent()->setPieces($this->mirrorPieces($pieces));

        $game->setInitialFen(null);
    }
}
