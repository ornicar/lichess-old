<?php

namespace Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Chess\Square;

class Pawn extends Piece
{
    public function getClass()
    {
        return 'Pawn';
    }

    public function getDirection()
    {
        return $this->getPlayer()->isWhite() ? 1 : -1;
    }

    public function getBasicTargetSquares()
    {
        $mySquare = $this->getSquare();
        $squares = array();

        $direction = $this->getDirection();

        $squares[] = $mySquare->getSquareByRelativePos(0, $direction);

        if (!$this->hasMoved())
        {
            $squares[] = $mySquare->getSquareByRelativePos(0, $direction*2);
        }

        $squares = $this->getBoard()->cleanSquares($squares);

        // can't eat forward
        foreach($squares as $it => $square)
        {
            if ($square->getPiece())
            {
                #TODO simplify that
                for($i=$it, $max = count($squares); $i<$max; $i++)
                {
                    unset($squares[$i]);
                }
            }
        }

        // eat
        foreach(array(-1, 1) as $dx)
        {
            if($square = $mySquare->getSquareByRelativePos($dx, $direction))
            {
                if ($piece = $square->getPiece())
                {
                    if ($piece->getPlayer() !== $this->getPlayer())
                    {
                        $squares[] = $square;
                    }
                }
            }
        }

        // en passant
        foreach(array(-1, 1) as $dx)
        {
            if (
                ($square = $mySquare->getSquareByRelativePos($dx, 0)) &&
                ($piece = $square->getPiece()) &&
                $piece->isClass('Pawn') &&
                $piece->getPlayer() !== $this->getPlayer() &&
                ($piece->getFirstMove() === ($this->getPlayer()->getGame()->getTurns() -1)) &&
                ($specialSquare = $mySquare->getSquareByRelativePos($dx, $direction)) &&
                $specialSquare->isEmpty()
            )
            {
                $squares[] = $specialSquare;
            }
        }

        return $squares;
    }
}
