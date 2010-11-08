<?php

namespace Bundle\LichessBundle\Document\Piece;
use Bundle\LichessBundle\Document\Piece;

/**
 * @mongodb:EmbeddedDocument
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
