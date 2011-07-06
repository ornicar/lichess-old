<?php

namespace Bundle\LichessBundle\Document\Piece;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Bundle\LichessBundle\Document\Piece;

/**
 * @MongoDB\EmbeddedDocument
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
