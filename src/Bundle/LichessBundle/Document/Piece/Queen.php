<?php

namespace Bundle\LichessBundle\Document\Piece;

use Bundle\LichessBundle\Document\Piece;

class Queen extends Piece
{
    public function getClass()
    {
        return 'Queen';
    }
}
