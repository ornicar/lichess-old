<?php

namespace Bundle\LichessBundle\Entity\Piece;
use Bundle\LichessBundle\Entity\Piece;
use Bundle\LichessBundle\Model\Piece as Model;

/**
 * @orm:Entity
 */
class Rook extends Piece implements Model\Rook
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
