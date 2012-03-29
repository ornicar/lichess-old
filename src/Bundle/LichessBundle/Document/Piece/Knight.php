<?php

namespace Bundle\LichessBundle\Document\Piece;

use Bundle\LichessBundle\Document\Piece;
use Bundle\LichessBundle\Chess\Board;

class Knight extends Piece
{
    public function getClass()
    {
        return 'Knight';
    }
}
