<?php

namespace Bundle\LichessBundle\Entity\Piece;
use Bundle\LichessBundle\Entity\Piece;

/**
 * @orm:Entity
 */
class Bishop extends Piece
{
    public function getClass()
    {
        return 'Bishop';
    }

    public function getBasicTargetKeys()
    {
        return array_merge(
            $this->getKeysByProjection(1, 1),
            $this->getKeysByProjection(1, -1),
            $this->getKeysByProjection(-1, 1),
            $this->getKeysByProjection(-1, -1)
        );
    }
}
