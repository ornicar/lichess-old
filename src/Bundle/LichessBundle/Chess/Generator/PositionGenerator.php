<?php

namespace Bundle\LichessBundle\Chess\Generator;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Piece;

abstract class PositionGenerator
{
    abstract public function createPieces(Game $game);

    protected function mirrorPieces(array $pieces)
    {
        $_pieces = array();
        foreach($pieces as $piece) {
            $_pieces[] = new Piece($piece->getX(), 9-$piece->getY(), $piece->getClass());
        }

        return $_pieces;
    }
}
