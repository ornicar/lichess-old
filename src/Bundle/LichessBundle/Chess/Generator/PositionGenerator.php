<?php

namespace Bundle\LichessBundle\Chess\Generator;

use Bundle\LichessBundle\Model\Game;
use Bundle\LichessBundle\Model\Player;
use Symfony\Component\DependencyInjection\ContainerAware;

abstract class PositionGenerator extends ContainerAware
{
    abstract public function createPieces(Game $game);

    public function mirrorPieces(array $pieces)
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
    public function createPiece($class, $x, $y)
    {
        $fullClass = $this->container->getParameter('lichess.model.piece.class') . '\\' . $class;

        $piece = new $fullClass($x, $y);

        return $piece;
    }
}
