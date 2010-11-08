<?php

namespace Bundle\LichessBundle\Document\Piece;
use Bundle\LichessBundle\Document\Piece;

/**
 * @mongodb:EmbeddedDocument
 */
class Queen extends Piece
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
