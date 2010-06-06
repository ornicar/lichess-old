<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Entities\Piece;

class Board
{
    protected
        $game,
        $squares,
        $pieces;

    public function __construct(Game $game)
    {
        $this->game = $game;
        $this->createSquares();
        $this->compile();
    }

    public function compile()
    {
        $this->pieces = array();

        foreach(PieceFilter::filterAlive($this->getPieces()) as $piece)
        {
            $this->pieces[$piece->getSquareKey()] = $piece;
        }
    }

    public function move(Piece $piece, $x, $y)
    {
        unset($this->pieces[$piece->getSquareKey()]);
        $piece->setX($x);
        $piece->setY($y);
        $this->pieces[$piece->getSquareKey()] = $piece;
    }

    public function remove(Piece $piece)
    {
        unset($this->pieces[$piece->getSquareKey()]);
    }

    public function add(Piece $piece)
    {
        $this->pieces[$piece->getSquareKey()] = $piece;
    }

    public function setGame(Game $game)
    {
        $this->game = $game;
    }

    /**
     * Dump the game to visual block notation like:
r bqkb r
 ppp ppp
p n  n  
    p   
B   P   
     N  
PPPP PPP
RNBQK  R
    */
    public function dump()
    {
        $string = "\n";
        for($y=8; $y>0; $y--) {
            for($x=1; $x<9; $x++) {
                if($piece = $this->getPieceByPos($x, $y)) {
                    $string .= $piece->getForsythe();
                }
                else {
                    $string .= ' ';
                }
            }
            $string .= "\n";
        }

        return $string;
    }

    public function getGame()
    {
        return $this->game;
    }

    public function getPlayers()
    {
        return $this->game->getPlayers();
    }

    public function getPieces()
    {
        return $this->game->getPieces();
    }

    public function getSquares()
    {
        return $this->squares;
    }

    public function getSquareKeys()
    {
        return array_keys($this->squares);
    }

    public function getSquareByKey($key)
    {
        return isset($this->squares[$key]) ? $this->squares[$key] : null;
    }

    public function getSquareByPos($x, $y)
    {
        if($x<1 || $x>8 || $y<1 || $y>8) {
            return null;
        }

        return $this->getSquareByKey($this->posToKey($x, $y));
    }

    public function getPieceByKey($key)
    {
        return isset($this->pieces[$key]) ? $this->pieces[$key] : null;
    }

    public function getPieceByPos($x, $y)
    {
        if($x<1 || $x>8 || $y<1 || $y>8) {
            return null;
        }

        return $this->getPieceByKey($this->posToKey($x, $y));
    }

    protected function createSquares()
    {
        $this->squares = array();

        for($x=1; $x<9; $x++)
        {
            for($y=1; $y<9; $y++)
            {
                $key = $this->posToKey($x, $y);
                $color = ($x+$y)%2 ? 'white' : 'black';
                $this->squares[$key] = new Square($this, $key, $x, $y, $color);
            }
        }
    }

    public function squaresToKeys(array $squares)
    {
        $keys = array();
        foreach($squares as $square) {
            $keys[] = $square->getKey();
        }

        return $keys;
    }

    /**
     * removes non existing or duplicated square
     */
    public function cleanSquares(array $squares, $passedKeys = array())
    {
        foreach($squares as $it => $square)
        {
            if($square instanceof Square)
            {
                $key = $square->getKey();
            }
            else
            {
                unset($squares[$it]);
                continue;
            }

            if(in_array($key, $passedKeys))
            {
                unset($squares[$it]);
            }
            else
            {
                $passedKeys[] = $key;
            }
        }

        return array_values($squares);
    }

    public function posToKey($x, $y)
    {
        static $xKeys = array(1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd', 5 => 'e', 6 => 'f', 7 => 'g', 8 => 'h');

        return $xKeys[$x].$y;
    }

    public function keyToPos($key)
    {
        static $xPos = array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6, 'g' => 7, 'h' => 8);

        return array($xPos[$key{0}], (int)$key{1});
    }

    public function __clone()
    {
        $this->createSquares();
    }
}
