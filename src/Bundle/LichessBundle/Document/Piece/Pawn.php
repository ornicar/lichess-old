<?php

namespace Bundle\LichessBundle\Document\Piece;
use Bundle\LichessBundle\Document\Piece;
use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Chess\Square;

/**
 * @mongodb:EmbeddedDocument
 */
class Pawn extends Piece
{
    public function getClass()
    {
        return 'Pawn';
    }

    public function getBasicTargetKeys()
    {
        $x = $this->x;
        $y = $this->y;
        $keys = array();
        $dy = 'white' === $this->color ? 1 : -1;

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

        // capture existing opponent piece

        $_y = $y+$dy;
        foreach(array(-1, 1) as $dx)
        {
            $_x = $x+$dx;
            if($_x<1 || $_x>8) {
                continue;
            }
            // capture
            $key = Board::posToKey($_x, $_y);
            if ($piece = $this->board->getPieceByKey($key))
            {
                if ($piece->getColor() !== $this->color)
                {
                    $keys[] = $key;
                }
            }
            // en passant
            if(5 === $y || 4 === $y) {
                $opponentKey = Board::posToKey($_x, $y);
                if (
                    ($piece = $this->board->getPieceByKey($opponentKey)) &&
                    $piece instanceof Pawn &&
                    $piece->getColor() !== $this->color &&
                    ($piece->getFirstMove() === ($this->getPlayer()->getGame()->getTurns() -1)) &&
                    !$this->board->hasPieceByKey($key)
                )
                {
                    $keys[] = $key;
                }
            }
        }

        return $keys;
    }

    public function getAttackTargetKeys()
    {
        $keys = array();
        $dy = 'white' === $this->color ? 1 : -1;

        $_y = $this->y+$dy;
        foreach(array(-1, 1) as $dx)
        {
            $_x = $this->x+$dx;
            if($_x<1 || $_x>8) {
                continue;
            }
            $key = Board::posToKey($_x, $_y);
            $piece = $this->board->getPieceByKey($key);
            if (!$piece || $piece->getColor() !== $this->color)
            {
                $keys[] = $key;
            }
        }

        return $keys;
    }
}
