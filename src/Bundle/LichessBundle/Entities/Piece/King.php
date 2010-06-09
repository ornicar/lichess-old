<?php

namespace Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Chess\Square;
use Bundle\LichessBundle\Chess\PieceFilter;
use Bundle\LichessBundle\Chess\MoveFilter;

class King extends Piece
{
    public function getClass()
    {
        return 'King';
    }

    public function getBasicTargetSquares()
    {
        $mySquare = $this->getSquare();

        $squares = array(
            $mySquare->getSquareByRelativePos(0, -1),
            $mySquare->getSquareByRelativePos(0, 1),
            $mySquare->getSquareByRelativePos(-1, -1),
            $mySquare->getSquareByRelativePos(-1, 0),
            $mySquare->getSquareByRelativePos(-1, 1),
            $mySquare->getSquareByRelativePos(1, -1),
            $mySquare->getSquareByRelativePos(1, 0),
            $mySquare->getSquareByRelativePos(1, 1)
        );

        return MoveFilter::filterCannibalism($this, $squares);
    }
}
