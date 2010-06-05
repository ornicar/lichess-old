<?php

namespace Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece;

class Rook extends Piece
{
    public function getClass()
    {
        return 'Rook';
    }

    public function getBasicTargetSquares()
    {
        return array_merge(
            $this->getTargetsByProjection(0, -1),
            $this->getTargetsByProjection(0, 1),
            $this->getTargetsByProjection(-1, 0),
            $this->getTargetsByProjection(+1, 0)
        );
    }
}
