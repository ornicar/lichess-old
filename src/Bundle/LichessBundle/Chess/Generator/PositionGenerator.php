<?php

namespace Bundle\LichessBundle\Chess\Generator;

use Bundle\LichessBundle\Model\Game;
use Bundle\LichessBundle\Model\Player;

abstract class PositionGenerator
{
    abstract public function createPieces(Game $game);

    protected function mirrorPieces(array $pieces)
    {
        $_pieces = array();
        foreach($pieces as $piece) {
            $_pieces[] = $this->createPiece($piece->getClass(), $piece->getX(), 9-$piece->getY());
        }

        return $_pieces;
    }

    /**
     * @return Piece
     */
    protected function createPiece($class, $x, $y)
    {
        $fullClass = 'Bundle\\LichessBundle\\Document\\Piece\\'.$class;

        $piece = new $fullClass($x, $y);

        return $piece;
    }
}
