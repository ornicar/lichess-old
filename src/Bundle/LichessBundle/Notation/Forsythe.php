<?php

namespace Bundle\LichessBundle\Notation;
use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Chess\Analyser;

class Forsythe
{

    /**
     * Transform a game to standart Forsyth Edwards Notation
     * http://en.wikipedia.org/wiki/Forsyth%E2%80%93Edwards_Notation
     */
    public function export(Game $game)
    {
        $board = $game->getBoard();
        $emptySquare = 0;
        $forsythe = '';

        for($y = 8; $y > 0; $y --)
        {
            for($x = 1; $x < 9; $x ++)
            {
                if ($piece = $board->getPieceByPos($x, $y))
                {
                    if ($emptySquare)
                    {
                        $forsythe .= $emptySquare;
                        $emptySquare = 0;
                    }

                    $forsythe .= $this->pieceToForsythe($piece);
                }
                else
                {
                    ++$emptySquare;
                }
            }
            if ($emptySquare)
            {
                $forsythe .= $emptySquare;
                $emptySquare = 0;
            }
            $forsythe .= '/';
        }

        $forsythe = trim($forsythe, '/');

        // b ou w to indicate turn
        $forsythe .= ' ';
        $forsythe .= $game->getTurns()%2 ? 'b' : 'w';

        // possibles castles
        $forsythe .= ' ';
        $hasCastle = false;
        $analyser = new Analyser($board);
        foreach($game->getPlayers() as $player)
        {
            if ($analyser->canCastleKingside($player))
            {
                $hasCastle = true;
                $forsythe .= $player->isWhite() ? 'K' : 'k';
            }
            if ($analyser->canCastleQueenside($player))
            {
                $hasCastle = true;
                $forsythe .= $player->isWhite() ? 'Q' : 'q';
            }
        }
        if (!$hasCastle)
        {
            $forsythe .= '-';
        }

        return $forsythe;
    }

    public function diffToMove(Game $game, $forsythe)
    {
        $moves = array(
            'from'  => array(),
            'to'    => array()
        );

        $x = 1;
        $y = 8;

        $board = $game->getBoard();
        $forsythe = str_replace('/', '', preg_replace('#\s*([\w\d/]+)\s.+#i', '$1', $forsythe));

        for($itForsythe = 0, $forsytheLen = strlen($forsythe); $itForsythe < $forsytheLen; $itForsythe++)
        {
            $letter = $forsythe{$itForsythe};
            $key = Board::posToKey($x, $y);

            if (is_numeric($letter))
            {
                for($x = $x, $max = $x+intval($letter); $x < $max; $x++)
                {
                    $_key = Board::posToKey($x, $y);
                    if (!$board->getSquareByKey($_key)->isEmpty())
                    {
                        $moves['from'][] = $_key;
                    }
                }
            }
            else
            {
                $color = ctype_lower($letter) ? 'black' : 'white';

                switch(strtolower($letter))
                {
                    case 'p': $class = 'Pawn'; break;
                    case 'r': $class = 'Rook'; break;
                    case 'n': $class = 'Knight'; break;
                    case 'b': $class = 'Bishop'; break;
                    case 'q': $class = 'Queen'; break;
                    case 'k': $class = 'King'; break;
                }

                if ($piece = $board->getPieceByKey($key))
                {
                    if($class != $piece->getClass() || $color !== $piece->getColor())
                    {
                        $moves['to'][] = $key;
                    }
                }
                else
                {
                    $moves['to'][] = $key;
                }

                ++$x;
            }

            if($x > 8)
            {
                $x = 1;
                --$y;
            }
        }

        if(empty($moves['from'])) {
            return null;
        }
        // two pieces moved: it's a castle
        if(2 === count($moves['from']))
        {
            if ($board->getPieceByKey($moves['from'][0])->isClass('King'))
            {
                $from = $moves['from'][0];
            }
            else
            {
                $from = $moves['from'][1];
            }

            if (in_array($board->getSquareByKey($moves['to'][0])->getX(), array(3, 7)))
            {
                $to = $moves['to'][0];
            }
            else
            {
                $to = $moves['to'][1];
            }
        }
        else {
            $from = $moves['from'][0];
            $to = $moves['to'][0];
        }

        return $from.' '.$to;
    }

    protected function pieceToForsythe(Piece $piece)
    {
        $class = $piece->getClass();

        if ('Knight' === $class)
        {
            $notation = 'N';
        }
        else
        {
            $notation = $class{0};
        }

        if('black' === $piece->getColor())
        {
            $notation = strtolower($notation);
        }

        return $notation;
    }
}
