<?php

namespace Bundle\LichessBundle\Document\Piece;
use Bundle\LichessBundle\Document\Piece;
use Bundle\LichessBundle\Chess\Board;

/**
 * @mongodb:EmbeddedDocument
 */
class Knight extends Piece
{
    public function getClass()
    {
        return 'Knight';
    }

    public function getBasicTargetKeys()
    {
        $mySquare = $this->getSquare();
        $x = $mySquare->getX();
        $y = $mySquare->getY();
        $keys = array();
        $board = $this->getBoard();

        /**
         * That's ugly and could be done easily in a nicer way.
         * But I needed performance optimization.
         */
        static $deltas = array(
            array(-1, 2),
            array(1, -2),
            array(2, -1),
            array(2, 1),
            array(1, 2),
            array(-1, -2),
            array(-2, 1),
            array(-2, -1)
        );
        foreach($deltas as $delta) {
            $_x = $x+$delta[0];
            $_y = $y+$delta[1];
            if($_x>0 && $_x<9 && $_y>0 && $_y<9) {
                $key = Board::posToKey($_x, $_y);
                if(($piece = $board->getPieceByKey($key)) && $piece->getPlayer() === $this->player) {
                    continue;
                }
                $keys[] = $key;
            }
        }
        return $keys;
    }
}
