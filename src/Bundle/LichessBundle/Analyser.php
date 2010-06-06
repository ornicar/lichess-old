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
        $this->board = board;
        $this->game = $board->getGame();
    }

    public function isKingAttacked(Player $player)
    {
        return in_array($player->getKing()->getSquareKey(), $this->getPlayerControlledKeys($player->getOpponent()));
    }

    /**
     * @return array flat array of keys
     */
    public function getPlayerControlledKeys(Player $player)
    {
        $controlledKeys = array();
        foreach(PieceFilter::filterAlive($player->getPieces()) as $piece)
        {
            // avoid infinite recursion
            if(!$piece instanceof Piece\King) {
                $controlledKeys = array_merge($controlledKeys, $this->getPieceControlledKeys());
            }
        }
        return $controlledKeys;
    }

    /**
     * @return array key => array keys
     */
    public function getPlayerPossibleMoves(Player $player)
    {
        $possibleMoves = array();
        $isKingAttacked = $this->isKingAttacked($player);
        $allOpponentPieces = PieceFilter::filterAlive($player->getOpponent()->getPieces());
        $projectionOpponentPieces = PieceFilter::filterProjection($allOpponentPieces);
        $king = $player->getKing();
        $kingSquareKey = $king->getSquareKey();
        foreach(PieceFilter::filterAlive($player->getPieces()) as $piece)
        {
            $pieceOriginalX = $piece->getX();
            $pieceOriginalY = $piece->getY();
            //if we are not moving the king, and the king is not attacked, don't check pawns nor knights
            if (!$piece instanceof King && !$kingIsAttacked) {
                $opponentPieces = $projectionOpponentPieces;
            }
            else {
                $opponentPieces = $allOpponentPieces;
            }

            $squares = $this->board->cleanSquares($piece->getBasicTargetSquares());
            foreach($squares as $it => $square)
            {
                // kings move to its target so we update its position
                if ($piece instanceof King)
                {
                    $kingSquareKey = $square->getKey();
                }

                // kill opponent piece
                if ($killedPiece = $square->getPiece())
                {
                    $killedPiece->setIsDead(true);
                }

                $board->move($piece, $square->getX(), $square->getY());

                foreach($opponentPieces as $opponentPiece)
                {
                    if($opponentPiece instanceof King) {
                        continue;
                    }
                    if (null !== $killedPiece && $opponentPiece->getIsDead())
                    {
                        continue;
                    }

                    // if our king gets attacked
                    if (in_array($kingSquareKey, $this->getPieceControlledKeys($opponentPiece)))
                    {
                        // can't go here
                        unset($squares[$it]);
                        break;
                    }
                }

                $this->board->move($piece, $pieceOriginalX, $pieceOriginalY);

                // if a virtual piece has been killed, bring it back to life
                if ($killedPiece)
                {
                    $killedPiece->setIsDead(false);
                    $this->board->add($killedPiece);
                }
            }
            if(!empty($squares)) {
                $possibleMoves[] = $this->board->squareToKeys($squares);
            }
        }
        return $possibleMoves;
    }

    /**
     * @return array flat array of keys
     */
    public function getPieceControlledKeys(Piece $piece)
    {
        return $this->board->squaresToKeys($this->board->cleanSquares($piece->getBasicTargetSquares()));
    }

    /**
     * @return array flat array of keys
     */
    public function getPiecePossibleMoves(Piece $piece, $isKingAttacked)
    {
        $playerPossibleMoves = $this->getPlayerPossibleMoves($piece->getPlayer());
        $key = $piece->getSquareKey();
        return isset($playerPossibleMoves[$key]) ? $playerPossibleMoves[$key] : null;
    }
}
