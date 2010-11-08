<?php

namespace Bundle\LichessBundle\Document\Piece;
use Bundle\LichessBundle\Document\Piece;

/**
 * @mongodb:EmbeddedDocument
 */
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
