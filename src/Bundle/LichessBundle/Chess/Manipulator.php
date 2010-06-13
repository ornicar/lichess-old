<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece\King;
use Bundle\LichessBundle\Entities\Piece\Pawn;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Stack;

class Manipulator
{
    /**
     * The board to manipulate
     *
     * @var Board
     */
    protected $board = null;

    /**
     * The event stack to record chess events
     *
     * @var Stack
     */
    protected $stack = null;

    protected $game = null;

    public function __construct(Board $board, Stack $stack = null)
    {
        $this->board = $board;
        $this->game = $board->getGame();
        $this->analyser = new Analyser($this->board);
        $this->stack = $stack;
    }

    public function play($notation, array $options = array())
    {
        $this->move($notation, $options);

        $player = $this->game->getTurnPlayer();
        $opponent = $player->getOpponent();
        $isOpponentKingAttacked = $this->analyser->isKingAttacked($opponent);
        if($isOpponentKingAttacked && $this->stack) {
            $this->stack->add(array(
                'type' => 'check',
                'key'  => $opponent->getKing()->getSquareKey()
            ));
        }
        $this->game->addTurn();
        $opponentPossibleMoves = $this->analyser->getPlayerPossibleMoves($opponent, $isOpponentKingAttacked);
        if(empty($opponentPossibleMoves)) {
            $this->game->setIsFinished(true);
            if($isOpponentKingAttacked) {
                $player->setIsWinner(true);
            }
        }

        return $opponentPossibleMoves;
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

        $possibleMoves = $this->analyser->getPiecePossibleMoves($piece);

        if(!$possibleMoves) {
            throw new \LogicException($piece.' can not move');
        }

        if(!in_array($to->getKey(), $possibleMoves)) {
            throw new \LogicException($piece.' can not go to '.$to.' ('.implode(',', $possibleMoves).')');
        }

        if($killed = $to->getPiece()) {
            $killed->setIsDead(true);
            $this->board->remove($killed);
        }

        $this->board->move($piece, $to->getX(), $to->getY());

        if($this->stack) {
            $this->stack->add(array(
                'type' => 'move',
                'from' => $from->getKey(),
                'to'   => $to->getKey()
            ));
        }

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
        $passedSquare = $to->getSquareByRelativePos(0, $pawn->getPlayer()->isWhite() ? -1 : 1); 
        $killed = $passedSquare->getPiece();

        if(!$killed || $killed->getPlayer() === $pawn->getPlayer()) {
            throw new \LogicException('Can not enpassant to '.$to);
        }

        $killed->setIsDead(true);
        $this->board->remove($killed);

        if($this->stack) {
            $this->stack->add(array(
                'type' => 'enpassant',
                'killed' => $passedSquare->getKey()
            ));
        }
    }

    /**
     * Handle pawn promotion
     **/
    protected function promotion(Pawn $pawn, array $options)
    {
        if(!isset($options['promotion'])) {
            throw new \InvalidArgumentException('You must provide promotion class');
        }
        $promotionClass = ucfirst($options['promotion']);
        if(!in_array($promotionClass, array('Queen', 'Knight', 'Bishop', 'Rook'))) {
            throw new \InvalidArgumentException('Bad promotion class: '.$promotionClass);
        }
        $player = $pawn->getPlayer();

        $this->board->remove($pawn);
        $player->removePiece($pawn);

        $fullClass = 'Bundle\\LichessBundle\\Entities\\Piece\\'.$promotionClass;
        $new = new $fullClass($pawn->getX(), $pawn->getY());
        $new->setPlayer($player);
        $new->setBoard($player->getGame()->getBoard());
        $player->addPiece($new);
        $this->board->add($new);

        if($this->stack) {
            $this->stack->add(array(
                'type' => 'promotion',
                'class' => strtolower($promotionClass),
                'key' => $new->getSquareKey()
            ));
        }
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

        if($this->stack) {
            $this->stack->add(array(
                'type' => 'castling',
                'from' => $rookSquare->getKey(),
                'to'   => $newRookSquare->getKey()
            ));
        }
    }

    /**
     * Get stack
     * @return Stack
     */
    public function getStack()
    {
      return $this->stack;
    }
    
    /**
     * Set stack
     * @param  Stack
     * @return null
     */
    public function setStack($stack)
    {
      $this->stack = $stack;
    }
}
