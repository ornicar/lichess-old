<?php

namespace Bundle\LichessBundle\Chess;

class Manipulator
{
    /**
     * The board to manipulate
     *
     * @var Board
     */
    protected $board = null;

    public function __construct(Board $board)
    {
        $this->board = $board;
    }

    /**
     * Move a piece on the board
     * Performs several validation before applying the move 
     * 
     * @param mixed $notation Valid algebraic notation (e.g. "a2 a4") 
     * @return void
     */
    public function move($notation)
    {
        list($from, $to) = explode(' ', $notation);

        if(!$from = $this->board->getSquareByKey($from)) {
            throw new \InvalidArgumentException('Square '.$from.' does not exist');
        }
        
        if(!$to = $this->board->getSquareByKey($to)) {
            throw new \InvalidArgumentException('Square '.$to.' does not exist');
        }

        if(!$piece = $from->getPiece()) {
            throw new \InvalidArgumentException('No piece on '.$from);
        }

        if(!$piece->getPlayer()->isMyTurn()) {
            throw new \LogicException('Not '.$piece->getColor().' player turn');
        }

        if(!in_array($to->getKey(), $piece->getTargetKeys())) {
            throw new \LogicException('Piece on '.$from.' can not go to '.$to);
        }

        $piece->setX($to->getX());
        $piece->setY($to->getY());
    }
}
