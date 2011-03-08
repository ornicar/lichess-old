<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Game;

class Autodraw
{
    /**
     * Return true if the game can not be won anymore
     * and can be declared as draw automatically
     *
     * @return boolean
     **/
    public function isAutodraw(Game $game)
    {
        $white = $game->getPlayer('white');
        $black = $game->getPlayer('black');
        $whiteNbPieces = $white->getNbAlivePieces();
        $blackNbPieces = $black->getNbAlivePieces();

        if (2 < $whiteNbPieces || 2 < $blackNbPieces) {
            return false;
        }

        if(1 === $whiteNbPieces && 1 === $blackNbPieces) {
            return true;
        }

        $whitePiece = $this->getLastPiece($white);
        $blackPiece = $this->getLastPiece($black);

        if ($this->canNotMate($white) && $this->canNotMate($black)) {
            return true;
        }

        return false;
    }

    protected function canNotMate(Player $player)
    {
        $lastPiece = $this->getLastPiece($player);

        return !$lastPiece || $lastPiece->isClass('Knight') || $lastPiece->isClass('Bishop');
    }

    protected function getLastPiece(Player $player)
    {
        $pieces = PieceFilter::filterNotClass(PieceFilter::filterAlive($white->getPieces()), 'King');

        return empty($pieces) ? null : $pieces[0];
    }
}
