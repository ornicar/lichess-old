<?php

namespace Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece;

class Bishop extends Piece
{
    public function getClass()
    {
        return 'Bishop';
    }

    public function getBasicTargetSquares()
    {
        return array_merge(
            $this->getTargetsByProjection(1, 1),
            $this->getTargetsByProjection(1, -1),
            $this->getTargetsByProjection(-1, 1),
            $this->getTargetsByProjection(-1, -1)
        );
    }
}
