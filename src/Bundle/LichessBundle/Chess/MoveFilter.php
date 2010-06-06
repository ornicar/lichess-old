<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Chess\Square;
use Bundle\LichessBundle\Chess\PieceFilter;
use Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece\King;

class MoveFilter
{
    // prevent a piece to eat a friend
    public static function filterCannibalism(Piece $piece, array $targets)
    {
        $player = $piece->getPlayer();
        foreach($targets as $it => $target)
        {
            if ($target && ($otherPiece = $target->getPiece()) && ($otherPiece->getPlayer() === $player))
            {
                unset($targets[$it]);
            }
        }

        return $targets;
    }

    // prevent leaving the king without protection
    public static function CLONEfilterProtectKing(Piece $piece, array $targets)
    {
        if(empty($targets))
        {
            return $targets;
        }

        $player = $piece->getPlayer();
        $king = $player->getKing();
        $kingSquareKey = $king->getSquareKey();

        // create virtual objects
        $_game        = $player->getGame()->getClone();
        $_board       = $_game->getBoard();
        $_pieceSquare  = $_board->getSquareByKey($piece->getSquareKey());
        $_piece        = $_pieceSquare->getPiece();
        $_player      = $_piece->getPlayer();
        $_opponent    = $_player->getOpponent();

        // if we are moving the king, or if king is attacked, verify every opponent pieces
        if ($_piece instanceof King || $king->isAttacked())
        {
            $_opponentPieces = PieceFilter::filterAlive($_opponent->getPieces());
        }
        // otherwise only verify projection pieces: bishop, rooks and queens
        else
        {
            $_opponentPieces = PieceFilter::filterAlive(PieceFilter::filterProjection($_opponent->getPieces()));
        }

        foreach($targets as $it => $square)
        {
            $_square = $_board->getSquareByKey($square->getKey());

            // kings move to its target
            if ($_piece instanceof King)
            {
                $kingSquareKey = $square->getKey();
            }

            // kill opponent piece
            if ($_killedPiece = $_square->getPiece())
            {
                $_killedPiece->kill();
            }

            $_piece->setX($_square->getX());
            $_piece->setY($_square->getY());

            $_board->compile();

            foreach($_opponentPieces as $_opponentPiece)
            {
                if ($_opponentPiece->getIsDead())
                {
                    continue;
                }

                // if our king gets attacked
                if (in_array($kingSquareKey, $_opponentPiece->getTargetKeys(false)))
                {
                    // can't go here
                    unset($targets[$it]);
                    break;
                }
            }

            // if a virtual piece has been killed, bring it back to life
            if ($_killedPiece)
            {
                $_killedPiece->setIsDead(false);
                $_killedPiece->setX($_square->getX());
                $_killedPiece->setY($_square->getY());
            }
        }

        // restore position
        $_piece->setX($piece->getX());
        $_piece->setY($piece->getY());

        return $targets;
    }

    //// prevent leaving the king without protection
    //public static function filterProtectKing(Piece $piece, array $targets)
    //{
        //if(empty($targets))
        //{
            //return $targets;
        //}

        //$player = $piece->getPlayer();
        //$board = $player->getGame()->getBoard();
        //$king = $player->getKing();
        //$kingSquareKey = $king->getSquareKey();
        //$pieceOriginalX = $piece->getX();
        //$pieceOriginalY = $piece->getY();
        //$opponentPieces = PieceFilter::filterAlive($player->getOpponent()->getPieces());
        ////if we are not moving the king, and the king is not attacked, don't check pawns nor knights
        //if (!$piece instanceof King && !$king->isAttacked())
        //{
            //$opponentPieces = PieceFilter::filterProjection($player->getOpponent()->getPieces());
        //}

        //foreach($targets as $it => $square)
        //{
            //// kings move to its target so we update its position
            //if ($piece instanceof King)
            //{
                //$kingSquareKey = $square->getKey();
            //}

            //// kill opponent piece
            //if ($killedPiece = $square->getPiece())
            //{
                //$killedPiece->setIsDead(true);
            //}

            //$board->move($piece, $square->getX(), $square->getY());

            //foreach($opponentPieces as $opponentPiece)
            //{
                //if($opponentPiece instanceof King) {
                    //continue;
                //}
                //if (null !== $killedPiece && $opponentPiece->getIsDead())
                //{
                    //continue;
                //}

                //// if our king gets attacked
                //if (in_array($kingSquareKey, $opponentPiece->getTargetKeys(false, false)))
                //{
                    //// can't go here
                    //unset($targets[$it]);
                    //break;
                //}
            //}

            //$board->move($piece, $pieceOriginalX, $pieceOriginalY);

            //// if a virtual piece has been killed, bring it back to life
            //if ($killedPiece)
            //{
                //$killedPiece->setIsDead(false);
                //$board->add($killedPiece);
            //}
        //}

        //return $targets;
    //}

}
