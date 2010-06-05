<?php

namespace Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece;

class Queen extends Piece
{
    public function getClass()
    {
        return 'Queen';
    }

    public function getBasicTargetSquares()
    {
        return array_merge(
            //bishop
            $this->getTargetsByProjection(1, 1),
            $this->getTargetsByProjection(1, -1),
            $this->getTargetsByProjection(-1, 1),
            $this->getTargetsByProjection(-1, -1),
            //rook
            $this->getTargetsByProjection(0, -1),
            $this->getTargetsByProjection(0, 1),
            $this->getTargetsByProjection(-1, 0),
            $this->getTargetsByProjection(+1, 0)
        );
    }
}
