<?php

namespace Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece;

class King extends Piece
{
    public function getClass()
    {
        return 'King';
    }

    protected function getBasicTargetSquares()
    {
        return array();
    }
}
