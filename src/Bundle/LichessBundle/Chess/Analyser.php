<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Entities\Player;
use Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece\King;

class Analyser
{
    /**
     * The board to analyse
     *
     * @var Board
     */
    protected $board = null;

    /**
     * Get board
     * @return Board
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * Set board
     * @param  Board
     * @return null
     */
    public function setBoard($board)
    {
        $this->board = $board;
    }

    public function __construct(Board $board)
    {
        $this->board = $board;
        $this->game = $board->getGame();
    }

    public function isKingAttacked(Player $player)
    {
        return in_array($player->getKing()->getSquareKey(), $this->getPlayerControlledKeys($player->getOpponent(), false));
    }

    /**
     * @return array flat array of keys
     */
    public function getPlayerControlledKeys(Player $player, $includeKing = true)
    {
        $controlledKeys = array();
        foreach(PieceFilter::filterAlive($player->getPieces()) as $piece)
        {
            if($includeKing || !$piece instanceof King) {
                $controlledKeys = array_merge($controlledKeys, $this->getPieceControlledKeys($piece));
            }
        }
        return $controlledKeys;
    }

    /**
     * @return array key => array keys
     */
    public function getPlayerPossibleMoves(Player $player, $isKingAttacked = null)
    {
        $possibleMoves = array();
        $isKingAttacked = null === $isKingAttacked ? $this->isKingAttacked($player) : $isKingAttacked;
        $allOpponentPieces = PieceFilter::filterAlive($player->getOpponent()->getPieces());
        $attackOpponentPieces = PieceFilter::filterNotClass($allOpponentPieces, 'King');
        $projectionOpponentPieces = PieceFilter::filterProjection($allOpponentPieces);
        $king = $player->getKing();
        foreach(PieceFilter::filterAlive($player->getPieces()) as $piece)
        {
            $kingSquareKey = $king->getSquareKey();
            $pieceOriginalX = $piece->getX();
            $pieceOriginalY = $piece->getY();
            $pieceClass = $piece->getClass();
            //if we are not moving the king, and the king is not attacked, don't check pawns nor knights
            if('King' === $pieceClass) {
                $opponentPieces = $allOpponentPieces;
            }
            elseif($isKingAttacked) {
                $opponentPieces = $attackOpponentPieces;
            }
            else {
                $opponentPieces = $projectionOpponentPieces;
            }

            $squares = $this->board->keysToSquares($piece->getBasicTargetKeys());
            if('King' === $pieceClass && !$isKingAttacked && !$piece->hasMoved()) {
                $squares = $this->addCastlingSquares($piece, $squares);
            }
            foreach($squares as $it => $square)
            {
                // king move to its target so we update its position
                if ('King' === $pieceClass)
                {
                    $kingSquareKey = $square->getKey();
                }

                // kill opponent piece
                if ($killedPiece = $square->getPiece())
                {
                    $killedPiece->setIsDead(true);
                }
                // kill with en passant
                elseif('Pawn' === $pieceClass && $square->getX() !== $pieceOriginalX)
                {
                    $killedPiece = $square->getSquareByRelativePos(0, $player->isWhite() ? -1 : 1)->getPiece();
                    $killedPiece->setIsDead(true);
                }

                $this->board->move($piece, $square->getX(), $square->getY());

                foreach($opponentPieces as $opponentPiece)
                {
                    if (null !== $killedPiece && $opponentPiece->getIsDead())
                    {
                        continue;
                    }

                    // if our king gets attacked
                    if (in_array($kingSquareKey, $opponentPiece->getAttackTargetKeys()))
                    {
                        // can't go here
                        unset($squares[$it]);
                        break;
                    }
                }

                $this->board->move($piece, $pieceOriginalX, $pieceOriginalY);

                // if a piece has been killed, bring it back to life
                if ($killedPiece)
                {
                    $killedPiece->setIsDead(false);
                    $this->board->add($killedPiece);
                }
            }
            if(!empty($squares)) {
                $possibleMoves[$piece->getSquareKey()] = $this->board->squaresToKeys($squares);
            }
        }
        return $possibleMoves;
    }

    public function getPiecePossibleMoves(Piece $piece)
    {
        $player = $piece->getPlayer();
        $isKingAttacked = $this->isKingAttacked($player);
        $allOpponentPieces = PieceFilter::filterNotClass(PieceFilter::filterAlive($player->getOpponent()->getPieces()), 'King');
        $king = $player->getKing();
        $kingSquareKey = $king->getSquareKey();
        $pieceOriginalX = $piece->getX();
        $pieceOriginalY = $piece->getY();
        $pieceClass = $piece->getClass();
        $opponentPieces = PieceFilter::filterProjection($allOpponentPieces);
        //if we are not moving the king, and the king is not attacked, don't check pawns nor knights
        if ('King' !== $pieceClass && !$isKingAttacked) {
            $opponentPieces = PieceFilter::filterProjection($opponentPieces);
        }
        $squares = $this->board->keysToSquares($piece->getBasicTargetKeys());
        if('King' === $pieceClass && !$isKingAttacked && !$piece->hasMoved()) {
            $squares = $this->addCastlingSquares($piece, $squares);
        }
        foreach($squares as $it => $square)
        {
            // kings move to its target so we update its position
            if ('King' === $pieceClass)
            {
                $kingSquareKey = $square->getKey();
            }

            // kill opponent piece
            if ($killedPiece = $square->getPiece())
            {
                $killedPiece->setIsDead(true);
            }
            // kill with en passant
            elseif('Pawn' === $pieceClass && $square->getX() !== $piece->getX())
            {
                $killedPiece = $square->getSquareByRelativePos(0, $player->isWhite() ? -1 : 1)->getPiece();
                $killedPiece->setIsDead(true);
            }

            $this->board->move($piece, $square->getX(), $square->getY());

            foreach($opponentPieces as $opponentPiece)
            {
                if (null !== $killedPiece && $opponentPiece->getIsDead())
                {
                    continue;
                }

                // if our king gets attacked
                if (in_array($kingSquareKey, $this->getPieceControlledKeys($opponentPiece, 'King' === $pieceClass)))
                {
                    // can't go here
                    unset($squares[$it]);
                    break;
                }
            }

            $this->board->move($piece, $pieceOriginalX, $pieceOriginalY);

            // if a piece has been killed, bring it back to life
            if ($killedPiece)
            {
                $killedPiece->setIsDead(false);
                $this->board->add($killedPiece);
            }
        }
        return $this->board->squaresToKeys($squares);
    }

    /**
     * @return array flat array of keys
     */
    public function getPieceControlledKeys(Piece $piece)
    {
        return $piece->getBasicTargetKeys();
    }

    /**
     * Add castling moves if available
     *
     * @return array the squares where the king can go
     **/
    protected function addCastlingSquares(King $king, array $squares)
    {
        $player = $king->getPlayer();
        $rooks = PieceFilter::filterNotMoved(PieceFilter::filterClass(PieceFilter::filterAlive($player->getPieces()), 'Rook'));
        if(empty($rooks)) {
            return $squares;
        }
        $opponentControlledKeys = $this->getPlayerControlledKeys($player->getOpponent(), true);

        foreach($rooks as $rook)
        {
            $kingX = $king->getX();
            $kingY = $king->getY();
            $dx = $kingX > $rook->getX() ? -1 : 1;
            $possible = true;
            foreach(array($kingX+$dx, $kingX+2*$dx) as $_x) {
                $key = Board::postoKey($_x, $kingY);
                if ($this->board->hasPieceByKey($key) || in_array($key, $opponentControlledKeys)) {
                    $possible = false;
                    break;
                }
            }
            if($possible) {
                if(-1 === $dx && $this->board->hasPieceByKey(Board::postoKey($kingX-3, $kingY))) {
                }
                else {
                    $squares[] = $this->board->getSquareByKey($key);
                }
            }
        }
        return $squares;
    }

    /**
     * Tell whether or not this player can castle king side
     *
     * @return bool
     **/
    public function canCastleKingSide(Player $player)
    {
       return $this->canCastle($player->getKing(), 3);
    }

    /**
     * Tell whether or not this player can castle queen side
     *
     * @return bool
     **/
    public function canCastleQueenSide(Player $player)
    {
       return $this->canCastle($player->getKing(), -4);
    }

    protected function canCastle(King $king, $relativeX)
    {
        if(!$this->game->isStandartVariant()) {
            return false;
        }
        $_x = $king->getX() + $relativeX;
        return
        $_x > 0 && $_x < 9 &&
        !$king->hasMoved() &&
        ($rook = $this->board->getSquareByPos($king->getX()+$relativeX, $king->getY())->getPiece()) &&
        ($rook->isClass('Rook') && !$rook->hasMoved());
    }
}
