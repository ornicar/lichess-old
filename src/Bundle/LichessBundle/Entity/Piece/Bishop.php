<?php

namespace Bundle\LichessBundle\Entity\Piece;
use Bundle\LichessBundle\Entity\Piece;
use Bundle\LichessBundle\Model\Piece as Model;

/**
 * @orm:Entity
 */
class Bishop extends Piece implements Model\Bishop
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
