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

    protected function getDirection()
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

    public function postMove(Square $oldSquare, Square $square, array $options = array())
    {
        // handle promotion
        $lastY = $this->getPlayer()->isWhite() ? 8 : 1;

        if ($square->getY() == $lastY)
        {
            $type = 'queen';

            $this->getPlayer()->get('Pieces')->set($this->getPlayer()->get('Pieces')->search($this),
                $piece = dmDb::table('DmChess'.ucfirst($type))->create()
                ->set('x', $this->x)
                ->set('y', $this->y)
                ->set('type', $type)
            );

            $this->getEventDispatcher()->notify(new dmChessPawnPromotionEvent($this, 'dm.chess.pawn_promotion', array(
                'type'      => $type,
                'old_piece' => $this,
                'new_piece' => $piece,
                'square'    => $square
            )));
        }
        // en passant
        elseif (
            $square->getX() !== $oldSquare->getX() &&
            $square->isEmpty() &&
            ($passedSquare = $square->getSquareByRelativePos(0, -$this->getDirection())) &&
            ($piece = $passedSquare->getPiece()) &&
            !$piece->getPlayer()->is($this->getPlayer())
        )
        {
            $this->getEventDispatcher()->notify(new dmChessPawnEnPassantEvent($this, 'dm.chess.pawn_en_passant', array(
                'killer'    => $this,
                'killed'    => $piece,
                'square'    => $passedSquare
            )));

            $piece->kill(true);
        }
    }

}
