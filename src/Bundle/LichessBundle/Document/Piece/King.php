<?php

namespace Bundle\LichessBundle\Document\Piece;

use Bundle\LichessBundle\Document\Piece;
use Bundle\LichessBundle\Chess\Board;

class King extends Piece
{
    public function getClass()
    {
        return 'King';
    }
}
