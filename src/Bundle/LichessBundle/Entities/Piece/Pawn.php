<?php

namespace Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece;

class Pawn extends Piece
{
    public function getClass()
    {
        return 'Pawn';
    }
}
