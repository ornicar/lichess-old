<?php

namespace Bundle\LichessBundle\Entity\Piece;
use Bundle\LichessBundle\Entity\Piece;
use Bundle\LichessBundle\Model\Piece as Model;

/**
 * @orm:Entity
 */
class Queen extends Piece implements Model\Queen
{
    public function getClass()
    {
        return 'Queen';
    }

    public function getBasicTargetKeys()
    {
        return array_merge(
            //bishop
            $this->getKeysByProjection(1, 1),
            $this->getKeysByProjection(1, -1),
            $this->getKeysByProjection(-1, 1),
            $this->getKeysByProjection(-1, -1),
            //rook
            $this->getKeysByProjection(0, -1),
            $this->getKeysByProjection(0, 1),
            $this->getKeysByProjection(-1, 0),
            $this->getKeysByProjection(+1, 0)
        );
    }
}
