<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Piece;

class PieceFilter
{
    // remove dead pieces
    public static function filterAlive(array $pieces)
    {
        foreach($pieces as $it => $piece) {
            if ($piece->getIsDead()) {
                unset($pieces[$it]);
            }
        }

        return array_values($pieces);
    }

    // remove alive pieces
    public static function filterDead(array $pieces)
    {
        foreach($pieces as $it => $piece) {
            if (!$piece->getIsDead()) {
                unset($pieces[$it]);
            }
        }

        return array_values($pieces);
    }
}
