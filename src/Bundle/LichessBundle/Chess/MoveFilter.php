<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Entities\Piece;

class MoveFilter
{
    // prevent a piece to eat a friend
    public static function filterCannibalism(Piece $piece, array $targets)
    {
        $player = $piece->getPlayer();
        foreach($targets as $it => $target)
        {
            if ($target && ($otherPiece = $target->getPiece()) && ($otherPiece->getPlayer() === $player))
            {
                unset($targets[$it]);
            }
        }

        return $targets;
    }

}
