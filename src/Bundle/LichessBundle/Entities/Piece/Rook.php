<?php

namespace Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece;

class Rook extends Piece
{
    public function getClass()
    {
        return 'Rook';
    }

    public function getBasicTargetKeys()
    {
        return array_merge(
            $this->getKeysByProjection(0, -1),
            $this->getKeysByProjection(0, 1),
            $this->getKeysByProjection(-1, 0),
            $this->getKeysByProjection(1, 0)
        );
    }
}
