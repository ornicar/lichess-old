<?php

namespace Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Chess\Square;

class Pawn extends Piece
{
    public function getClass()
    {
        return 'Pawn';
    }

    public function getBasicTargetKeys()
    {
        $mySquare = $this->getSquare();
        $x = $mySquare->getX();
        $y = $mySquare->getY();
        $keys = array();
        $dy = $this->getPlayer()->isWhite() ? 1 : -1;

        $key = Board::posToKey($x, $y+$dy);
        if(!$this->board->hasPieceByKey($key)) {
            $keys[] = $key;
        }

        if (!$this->hasMoved() && !empty($keys))
        {
            $key = Board::posToKey($x, $y+(2*$dy));
            if(!$this->board->hasPieceByKey($key)) {
                $keys[] = $key;
            }
        }

        return array_merge($keys, $this->getAttackTargetKeys());
    }

    public function getAttackTargetKeys()
    {
        $keys = array();
        $mySquare = $this->board->getSquareByKey($this->getSquareKey());
        $x = $mySquare->getX();
        $y = $mySquare->getY();
        $dy = $this->getPlayer()->isWhite() ? 1 : -1;

        $_y = $y+$dy;
        foreach(array(-1, 1) as $dx)
        {
            $_x = $x+$dx;
            if($_x<1 || $_x>8) {
                continue;
            }
            // eat
            $key = Board::posToKey($_x, $_y);
            if ($piece = $this->board->getPieceByKey($key))
            {
                if ($piece->getPlayer() !== $this->getPlayer())
                {
                    $keys[] = $key;
                }
            }
            $opponentKey = Board::posToKey($_x, $y);
            // en passant
            if (
                ($piece = $this->board->getPieceByKey($opponentKey)) &&
                $piece instanceof Pawn &&
                $piece->getPlayer() !== $this->player &&
                ($piece->getFirstMove() === ($this->getPlayer()->getGame()->getTurns() -1)) &&
                !$this->board->hasPieceByKey($key)
            )
            {
                $keys[] = $key;
            }
        }

        return $keys;
    }
}
