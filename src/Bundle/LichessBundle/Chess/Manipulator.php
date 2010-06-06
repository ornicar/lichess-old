<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece\King;
use Bundle\LichessBundle\Entities\Piece\Pawn;
use Bundle\LichessBundle\Chess\Analyser;

class Manipulator
{
    /**
     * The board to manipulate
     *
     * @var Board
     */
    protected $board = null;

    protected $game = null;

    public function __construct(Board $board)
    {
        $this->board = $board;
        $this->game = $board->getGame();
        $this->analyser = new Analyser($this->board);
    }

    public function play($notation, array $options = array())
    {
        $this->game->clearCache();
        $this->move($notation, $options);

        $player = $this->game->getTurnPlayer();
        $possibleMoves = $analyser->getPlayerPossibleMoves($player->getOpponent());
        if(empty($possibleMoves)) {
            $this->game->setIsFinished(true);
            if($analyser->isKingAttacked($player->getOpponent())) {
                $player->setIsWinner(true);
            }
        }
        else {
            $this->game->addTurn();
        }

        return $possibleMoves;
    }

    /**
     * Move a piece on the board
     * Performs several validation before applying the move 
     * 
     * @param mixed $notation Valid algebraic notation (e.g. "a2 a4") 
     * @return void
     */
    public function move($notation, array $options = array())
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
            throw new \LogicException('Can not play '.$from.' '.$to.' - Not '.$piece->getColor().' player turn');
        }

        if(!in_array($to->getKey(), $this->analyser->getPiecePossibleMoves($piece))) {
            throw new \LogicException($piece.' can not go to '.$to.' ('.implode(',', $this->analyser->getPiecePossibleMoves($piece)).')');
        }

        if($killed = $to->getPiece()) {
            $killed->setIsDead(true);
            $this->board->remove($killed);
        }

        $this->board->move($piece, $to->getX(), $to->getY());

        if(null === $piece->getFirstMove()) {
            $piece->setFirstMove($this->game->getTurns());
        }

        // casting?
        if($piece instanceof King && 2 === abs($from->getX() - $to->getX())) {
            $this->castling($piece, $to);
        }

        // promotion?
        if($piece instanceof Pawn && ($to->getY() === ($piece->getPlayer()->isWhite() ? 8 : 1))) {
            $this->promotion($piece, $options);
        }

        // enpassant?
        if($piece instanceof Pawn && $to->getX() !== $from->getX() && !$killed) {
            $this->enpassant($piece, $to);
        }
    }

    /**
     * Handle pawn enpassant
     **/
    protected function enpassant(Pawn $pawn, Square $to)
    {
        $passedSquare = $to->getSquareByRelativePos(0, -$pawn->getDirection()); 
        $killed = $passedSquare->getPiece();

        if(!$killed || $killed->getPlayer() === $pawn->getPlayer()) {
            throw new \LogicException('Can not enpassant to '.$to);
        }

        $killed->setIsDead(true);
        $this->board->remove($killed);
    }

    /**
     * Handle pawn promotion
     **/
    protected function promotion(Pawn $pawn, array $options)
    {
        if(!in_array($options['promotion'], array('Queen', 'Knight', 'Bishop', 'Rook'))) {
            throw new \InvalidArgumentException('Bad promotion class: '.$options['promotion']);
        }
        $player = $pawn->getPlayer();

        $this->board->remove($pawn);
        $player->removePiece($pawn);

        $fullClass = 'Bundle\\LichessBundle\\Entities\\Piece\\'.$options['promotion'];
        $new = new $fullClass($pawn->getX(), $pawn->getY());
        $new->setPlayer($player);
        $player->addPiece($new);
        $this->board->add($new);
    }

    /**
     * Handle castling
     **/
    protected function castling(King $king, Square $to)
    {
        if (7 === $to->getX())
        {
            $rookSquare = $to->getSquareByRelativePos(1, 0);
            $newRookSquare = $to->getSquareByRelativePos(-1, 0);
        }
        else
        {
            $rookSquare = $to->getSquareByRelativePos(-2, 0);
            $newRookSquare = $to->getSquareByRelativePos(1, 0);
        }

        $rook = $rookSquare->getPiece();
        $this->board->move($rook, $newRookSquare->getX(), $newRookSquare->getY());
        $rook->setFirstMove($this->game->getTurns());
    }
}
