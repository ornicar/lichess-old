<?php

namespace Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece;

class Queen extends Piece
{
    public function getClass()
    {
        return 'Queen';
    }

    protected function getBasicTargetSquares()
    {
        return array();
    }
}
