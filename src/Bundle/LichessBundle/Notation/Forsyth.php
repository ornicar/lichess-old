<?php

namespace Bundle\LichessBundle\Notation;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Piece;
use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Chess\PieceFilter;

class Forsyth
{
    /**
     * Transform a game to standard Forsyth Edwards Notation
     * http://en.wikipedia.org/wiki/Forsyth%E2%80%93Edwards_Notation
     */
    public static function export(Game $game, $positionOnly = false)
    {
        static $reverseClasses = array('Pawn' => 'p', 'Rook' => 'r', 'Knight' => 'n', 'Bishop' => 'b', 'Queen' => 'q', 'King' => 'k');
        $board = $game->getBoard();
        $emptySquare = 0;
        $forsyth = '';

        for($y = 8; $y > 0; $y--) {
            for($x = 1; $x < 9; $x++) {
                if ($piece = $board->getPieceByPosNoCheck($x, $y)) {
                    if ($emptySquare) {
                        $forsyth .= $emptySquare;
                        $emptySquare = 0;
                    }
                    $notation = $reverseClasses[$piece->getClass()];
                    if('white' === $piece->getColor()) {
                        $notation = strtoupper($notation);
                    }
                    $forsyth .= $notation;
                } else {
                    ++$emptySquare;
                }
            }
            if ($emptySquare) {
                $forsyth .= $emptySquare;
                $emptySquare = 0;
            }
            $forsyth .= '/';
        }

        $forsyth = trim($forsyth, '/');

        if ($positionOnly) {
            return $forsyth;
        }

        // b ou w to indicate turn
        $forsyth .= ' ';
        $forsyth .= substr($game->getTurnColor(), 0, 1);

        // possibles castles
        $forsyth .= ' ';
        $forsyth .= $game->getCastles();

        // en passant
        $enPassant = '-';
        foreach(PieceFilter::filterClass(PieceFilter::filterAlive($game->getPieces()), 'Pawn') as $piece) {
            if($piece->getFirstMove() === ($game->getTurns() - 1)) {
                $color = $piece->getPlayer()->getColor();
                $y = $piece->getY();
                if(($color === 'white' && 4 === $y) || ($color === 'black' && 5 === $y)) {
                    $enPassant = Board::posToKey($piece->getX(), 'white' === $color ? $y - 1 : $y + 1);
                    break;
                }
            }
        }
        $forsyth .= ' '.$enPassant;

        // Halfmove clock: This is the number of halfmoves since the last pawn advance or capture.
        // This is used to determine if a draw can be claimed under the fifty-move rule.
        $forsyth .= ' '.$game->getHalfmoveClock();

        // Fullmove number: The number of the full move. It starts at 1, and is incremented after Black's move.
        $forsyth .= ' '.$game->getFullMoveNumber();

        return $forsyth;
    }

    /**
     * Create and position pieces of the game for the forsyth string
     *
     * @param Game $game
     * @param string $forsyth
     * @return Game $game
     */
    public static function import(Game $game, $forsyth)
    {
        static $classes = array('p' => 'Pawn', 'r' => 'Rook', 'n' => 'Knight', 'b' => 'Bishop', 'q' => 'Queen', 'k' => 'King');
        $x = 1;
        $y = 8;
        $board = $game->getBoard();
        $forsyth = str_replace('/', '', preg_replace('#\s*([\w\d/]+)\s.+#i', '$1', $forsyth));
        $pieces = array('white' => array(), 'black' => array());

        for($itForsyth = 0, $forsythLen = strlen($forsyth); $itForsyth < $forsythLen; $itForsyth++) {
            $letter = $forsyth{$itForsyth};

            if (is_numeric($letter)) {
                $x += intval($letter);
            } else {
                $color = ctype_lower($letter) ? 'black' : 'white';
                $pieces[$color][] = self::createPiece($classes[strtolower($letter)], $x, $y);
                ++$x;
            }

            if($x > 8) {
                $x = 1;
                --$y;
            }
        }

        foreach ($game->getPlayers() as $player) {
            $player->setPieces($pieces[$player->getColor()]);
        }
        $game->ensureDependencies();
    }

    protected static function pieceToForsyth(Piece $piece)
    {
        static $reverseClasses = array('Pawn' => 'p', 'Rook' => 'r', 'Knight' => 'n', 'Bishop' => 'b', 'Queen' => 'q', 'King' => 'k');

        $notation = $reverseClasses[$piece->getClass()];

        if('white' === $piece->getColor()) {
            $notation = strtoupper($notation);
        }

        return $notation;
    }

    /**
     * @return Piece
     */
    protected static function createPiece($class, $x, $y)
    {
        $fullClass = 'Bundle\\LichessBundle\\Document\\Piece\\'.$class;

        $piece = new $fullClass($x, $y);

        return $piece;
    }
}
