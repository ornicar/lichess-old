<?php

namespace Bundle\LichessBundle\Document\Piece;

use Bundle\LichessBundle\Document\Piece;
use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Chess\Square;

class Pawn extends Piece
{
    public function getClass()
    {
        return 'Pawn';
    }
}
