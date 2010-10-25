<?php

namespace Bundle\LichessBundle\Chess\Generator;

use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Notation\Forsyth;

class Chess960PositionGenerator extends PositionGenerator
{
    public function createPieces(Game $game)
    {
        $pieces = array();
        $player = $game->getPlayer('white');

        // Bishop on black square
        $file = 2*mt_rand(1, 4) - 1;
        $pieces[$file] = $this->createPiece('Bishop', $file, 1);

        // Bishop on white square
        $file = 2*mt_rand(1, 4);
        $pieces[$file] = $this->createPiece('Bishop', $file, 1);

        // Queen and Knights
        foreach(array(6 => 'Queen', 5 => 'Knight', 4 => 'Knight') as $rand => $class) {
            $file = $this->getEmptyFile($pieces, mt_rand(1, $rand));
            $pieces[$file] = $this->createPiece($class, $file, 1);
        }

        // Rooks and King
        foreach(array('Rook', 'King', 'Rook') as $class) {
            $file = $this->getEmptyFile($pieces, 1);
            $pieces[$file] = $this->createPiece($class, $file, 1);
        }

        // Pawns
        for($it=1; $it<=8; $it++) {
            $pieces[] = $this->createPiece('Pawn', $it, 2);
        }

        $pieces = array_values($pieces);
        $player->setPieces($pieces);
        $player->getOpponent()->setPieces($this->mirrorPieces($pieces));

        $forsyth = new Forsyth();
        $game->setInitialFen($forsyth->export($game));
    }

    protected function getEmptyFile($pieces, $num)
    {
        static $files = array(1, 2, 3, 4, 5, 6, 7, 8);
        $emptyFiles = array_values(array_diff($files, array_keys($pieces)));
        return $emptyFiles[$num-1];
    }
}
