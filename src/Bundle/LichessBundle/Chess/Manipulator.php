<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece\King;
use Bundle\LichessBundle\Entities\Piece\Pawn;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Notation\PgnDumper;
use Bundle\LichessBundle\Stack;
use Bundle\LichessBundle\Entities\Game;

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

    public function __construct(Game $game, Stack $stack = null)
    {
        $this->game = $game;
        $this->board = $game->getBoard();
        $this->analyser = new Analyser($this->board);
        $this->stack = $stack ? $stack : new Stack();
    }

    public function play($notation, array $options = array())
    {
        $pgn = $this->move($notation, $options);

        $player = $this->game->getTurnPlayer();
        $opponent = $player->getOpponent();
        $isOpponentKingAttacked = $this->analyser->isKingAttacked($opponent);
        if($isOpponentKingAttacked) {
            $this->stack->addEvent(array(
                'type' => 'check',
                'key'  => $opponent->getKing()->getSquareKey()
            ));
            $pgn .= '+';
        }
        $this->game->addTurn();
        $opponentPossibleMoves = $this->analyser->getPlayerPossibleMoves($opponent, $isOpponentKingAttacked);
        if(empty($opponentPossibleMoves)) {
            if($isOpponentKingAttacked) {
                $this->game->setStatus(Game::MATE);
                $player->setIsWinner(true);
                $pgn = preg_replace('/\+$/', '#', $pgn);
            }
            else {
                $this->game->setStatus(Game::STALEMATE);
            }
            $this->stack->addEvent(array('type' => 'end'));
        }
        elseif($this->game->isCandidateToAutoDraw()) {
            $this->game->setStatus(GAME::DRAW);
            $this->stack->addEvent(array('type' => 'end'));
        }

        $this->game->addPositionHash();
        if($this->game->isThreefoldRepetition()) {
            $this->stack->addEvent(array('type' => 'threefold_repetition'));
        }

        $this->game->addPgnMove($pgn);

        return $opponentPossibleMoves;
    }

    /**
     * Move a piece on the board
     * Performs several validation before applying the move 
     * 
     * @param mixed $notation Valid algebraic notation (e.g. "a2 a4") 
     * @return string PGN notation of the move
     */
    public function move($notation, array $options = array())
    {
        list($fromKey, $toKey) = explode(' ', $notation);

        if(!$from = $this->board->getSquareByKey($fromKey)) {
            throw new \InvalidArgumentException('Square '.$fromKey.' does not exist');
        }

        if(!$to = $this->board->getSquareByKey($toKey)) {
            throw new \InvalidArgumentException('Square '.$toKey.' does not exist');
        }

        if(!$piece = $from->getPiece()) {
            throw new \InvalidArgumentException('No piece on '.$from);
        }
        $pieceClass = $piece->getClass();

        $player = $piece->getPlayer();
        if(!$player->isMyTurn()) {
            throw new \LogicException('Can not play '.$from.' '.$to.' - Not '.$piece->getColor().' player turn');
        }

        $isPlayerKingAttacked = $this->analyser->isKingAttacked($player);
        $playerPossibleMoves = $this->analyser->getPlayerPossibleMoves($player, $isPlayerKingAttacked);
        $possibleMoves = isset($playerPossibleMoves[$fromKey]) ? $playerPossibleMoves[$fromKey] : false;

        if(!$possibleMoves) {
            throw new \LogicException($piece.' can not move');
        }

        if(!in_array($toKey, $possibleMoves)) {
            throw new \LogicException($piece.' can not go to '.$to.' ('.implode(',', $possibleMoves).')');
        }

        // killed?
        $killed = $to->getPiece();

        // casting?
        $isCastling = 'King' === $pieceClass && 2 === abs($from->getX() - $to->getX());

        // promotion?
        if('Pawn' === $pieceClass && ($to->getY() === ($player->isWhite() ? 8 : 1))) {
            $isPromotion = true;
            $promotionClass = isset($options['promotion']) ? ucfirst($options['promotion']) : 'Queen';
            if(!in_array($promotionClass, array('Queen', 'Knight', 'Bishop', 'Rook'))) {
                throw new \InvalidArgumentException('Bad promotion class: '.$promotionClass);
            }
            $options['promotion'] = $promotionClass;
        }
        else {
            $isPromotion = false;
        }

        // enpassant?
        $isEnPassant = 'Pawn' === $pieceClass && $to->getX() !== $from->getX() && !$killed;

        $pgnDumper = new PgnDumper();
        $pgn = $pgnDumper->dumpMove($this->game, $piece, $from, $to, $playerPossibleMoves, $killed, $isCastling, $isPromotion, $isEnPassant, $options);

        if($killed) {
            $killed->setIsDead(true);
            $this->board->remove($killed);
        }

        $this->board->move($piece, $to->getX(), $to->getY());

        $this->stack->addEvent(array(
            'type' => 'move',
            'from' => $from->getKey(),
            'to'   => $to->getKey()
        ));

        if(null === $piece->getFirstMove()) {
            $piece->setFirstMove($this->game->getTurns());
        }

        if($isCastling) {
            $this->castling($piece, $to);
        }

        if($isPromotion) {
            $this->promotion($piece, $options['promotion']);
        }

        if($isEnPassant) {
            $this->enpassant($piece, $to);
        }

        // When an irreversible event happens,
        // we can safely clear the game position hashes
        if($killed || $isPromotion || $isCastling || 'Pawn' === $pieceClass) {
            $this->game->clearPositionHashes();
        }

        return $pgn;
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

        $this->stack->addEvent(array(
            'type' => 'enpassant',
            'killed' => $passedSquare->getKey()
        ));
    }

    /**
     * Handle pawn promotion
     **/
    protected function promotion(Pawn $pawn, $promotionClass)
    {
        $player = $pawn->getPlayer();

        $this->board->remove($pawn);
        $player->removePiece($pawn);

        $fullClass = 'Bundle\\LichessBundle\\Entities\\Piece\\'.$promotionClass;
        $new = new $fullClass($pawn->getX(), $pawn->getY());
        $new->setPlayer($player);
        $new->setBoard($player->getGame()->getBoard());
        $player->addPiece($new);
        $this->board->add($new);

        $this->stack->addEvent(array(
            'type' => 'promotion',
            'pieceClass' => strtolower($promotionClass),
            'key' => $new->getSquareKey()
        ));
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

        $this->stack->addEvent(array(
            'type' => 'castling',
            'from' => $rookSquare->getKey(),
            'to'   => $newRookSquare->getKey()
        ));
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
