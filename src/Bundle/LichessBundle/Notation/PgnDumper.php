<?php

namespace Bundle\LichessBundle\Notation;
use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Chess\Square;

/**
 * http://www.chessclub.com/help/PGN-spec
 */
class PgnDumper
{
    /**
     * Dumps a single move to PGN notation
     *
     * @return string
     **/
    public function dumpMove(Game $game, Piece $piece, Square $from, Square $to, array $playerPossibleMoves, $killed, $isCastling, $isPromotion, $isEnPassant)
    {
        $board = $game->getBoard();
        $pieceClass = $piece->getClass();
        $fromKey = $from->getKey();
        $toKey = $to->getKey();

        if($isCastling) {
            if(3 === $to->getX()) {
                return 'O-O-O';
            }
            else {
                return 'O-O';
            }
        }
        $pgnFromPiece = $pgnFromFile = $pgnFromRank = '';
        if('Pawn' != $pieceClass) {
            $pgnFromPiece = $piece->getPgn();
            $candidates = array();
            foreach($playerPossibleMoves as $_from => $_tos) {
                if($_from !== $fromKey && in_array($toKey, $_tos)) {
                    $_piece = $board->getPieceByKey($_from);
                    if($_piece->getClass() === $pieceClass) {
                        $candidates[] = $_piece;
                    }
                }
            }
        }
        if($killed) {
            $pgnCapture = 'x';
            if('Pawn' === $pieceClass) {
                $pgnFromFile = $from->getFile();
            }
        }
        else {
            $pgnCapture = '';
        }
        $pgnTo = $to->getKey();

        $pgn = $pgnFromPiece.$pgnFromFile.$pgnFromRank.$pgnCapture.$pgnTo;

        return $pgn;
    }

    /**
     * Produces PGN notation for a game
     *
     * @return string
     **/
    public function dumpGame(Game $game)
    {
        $result = $this->getPgnResult($game);
        $header = $this->getPgnHeader($game);
        $moves = $this->getPgnMoves($game);

        $pgn = $header."\n\n".$moves;

        if(!empty($moves)) {
            $pgn .= ' ';
        }

        $pgn .= $result;

        return $pgn;
    }

    public function getPgnMoves(Game $game)
    {
        if('' == $game->getPgnMoves()) {
            return '';
        }
        $moves = explode(' ', $game->getPgnMoves());
        $nbMoves = count($moves);
        $nbTurns = ceil($nbMoves/2);
        $string = '';
        for($turns = 1; $turns <= $nbTurns; $turns++) {
            $string .= $turns.'.';
            $string .= $moves[($turns-1)*2].' ';
            if(isset($moves[($turns-1)*2+1])) {
                $string .= $moves[($turns-1)*2+1].' ';
            }
        }

        return trim($string);
    }

    protected function getPgnHeader(Game $game)
    {
        return sprintf('[Site "%s"]%s[Result "%s"]',
            'http://lichess.org/', "\n", $this->getPgnResult($game)
        );
    }

    protected function getPgnResult(Game $game)
    {
        if($game->getIsFinished()) {
            if($game->getPlayer('white')->getIsWinner()) {
                return '1-0';
            }
            elseif($game->getPlayer('black')->getIsWinner()) {
                return '0-1';
            }
            return '1/2-1/2';
        }
        return '*';
    }
}
