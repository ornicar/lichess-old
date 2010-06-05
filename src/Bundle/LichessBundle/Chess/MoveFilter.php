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
    public static function filterProtectKing(Piece $piece, array $targets)
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
        # TODO check if we can just use the piece square instead
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

}
