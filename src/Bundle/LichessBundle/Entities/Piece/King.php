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

        $debug = $this->getPlayer()->getGame()->getTurns() == 19 && 'black' == $this->getColor(); 

        // castles
        if (!$this->hasMoved() && !$this->isAttacked())
        {
            $opponent = $this->getPlayer()->getOpponent();

            foreach(PieceFilter::filterAlive(PieceFilter::filterClass($this->getPlayer()->getPieces(), 'Rook')) as $rook)
            {
                if (!$rook->hasMoved())
                {
                    $canCastle = true;
                    $dx = $this->getX() > $rook->getX() ? -1 : 1;
                    $squaresToRook = array($mySquare->getSquareByRelativePos($dx, 0), $mySquare->getSquareByRelativePos(2*$dx, 0)); 
                    foreach($squaresToRook as $square)
                    {
                        if (!$square->isEmpty() || $square->isControlledBy($opponent))
                        {
                            $canCastle = false;
                            break;
                        }
                    }
                    if ($canCastle)
                    {
                        $squares[] = $squaresToRook[1];
                    }
                }
            }
        }

        return MoveFilter::filterCannibalism($this, $squares);
    }

    public function canCastleQueenside()
    {
        return $this->canCastle(-4);
    }

    public function canCastleKingside()
    {
        return $this->canCastle(3);
    }

    protected function canCastle($relativeX)
    {
        return
            !$this->hasMoved() &&
            ($rook = $this->getSquare()->getSquareByRelativePos($relativeX, 0)->getPiece()) &&
            ($rook->isClass('Rook') && !$rook->hasMoved());
    }

    protected function getSquaresToRook(Rook $rook)
    {
        $squares = array();
        $rookSquare = $rook->getSquare();
        $kingSquare = $this->getSquare();

        $dx = $kingSquare->getX() > $rookSquare->getX() ? -1 : 1;

        $square = $kingSquare;
        while(($square = $square->getSquareByRelativePos($dx, 0)) && !$square->is($rookSquare))
        {
            $squares[] = $square;
        }

        return $squares;
    }
}
