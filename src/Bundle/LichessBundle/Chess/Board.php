<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Piece;
use Bundle\LichessBundle\Document\Piece\King;
use Bundle\LichessBundle\Document\Piece\Rook;

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

        foreach(PieceFilter::filterAlive($this->getPieces()) as $piece) {
            $this->pieces[$piece->getSquareKey()] = $piece;
        }
    }

    public function setGame(Game $game)
    {
        $this->game = $game;
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

    public function hasSquareByKey($key)
    {
        return isset($this->squares[$key]);
    }

    public function getSquareByPos($x, $y)
    {
        if($x<1 || $x>8 || $y<1 || $y>8) {
            return null;
        }

        return $this->getSquareByKey(self::posToKey($x, $y));
    }

    public function getPieceByKey($key)
    {
        return isset($this->pieces[$key]) ? $this->pieces[$key] : null;
    }

    public function hasPieceByKey($key)
    {
        return isset($this->pieces[$key]);
    }

    public function getPieceByPos($x, $y)
    {
        if($x<1 || $x>8 || $y<1 || $y>8) {
            return null;
        }

        return $this->getPieceByKey(self::posToKey($x, $y));
    }

    public function getPieceByPosNoCheck($x, $y)
    {
        return $this->getPieceByKey(self::posToKey($x, $y));
    }


    protected function createSquares()
    {
        $this->squares = array();

        for($x=1; $x<9; $x++) {
            for($y=1; $y<9; $y++) {
                $key = self::posToKey($x, $y);
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

    public function keysToSquares(array $keys)
    {
        $squares = array();
        foreach ($keys as $key) {
            $squares[] = $this->squares[$key];
        }

        return $squares;
    }

    public static function posToKey($x, $y)
    {
        static $xKeys = array(1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd', 5 => 'e', 6 => 'f', 7 => 'g', 8 => 'h');

        return $xKeys[$x].$y;
    }

    public static function keyToPos($key)
    {
        static $xPos = array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6, 'g' => 7, 'h' => 8);

        return array($xPos[$key{0}], (int)$key{1});
    }

    public static function piotrToKey($p)
    {
        return self::$piotr[$p];
    }

    public static function keyToPiotr($k)
    {
        return self::$rtoip[$k];
    }

    private static $piotr = array('a'=>'a1','b'=>'b1','c'=>'c1','d'=>'d1','e'=>'e1','f'=>'f1','g'=>'g1','h'=>'h1','i'=>'a2','j'=>'b2','k'=>'c2','l'=>'d2','m'=>'e2','n'=>'f2','o'=>'g2','p'=>'h2','q'=>'a3','r'=>'b3','s'=>'c3','t'=>'d3','u'=>'e3','v'=>'f3','w'=>'g3','x'=>'h3','y'=>'a4','z'=>'b4','A'=>'c4','B'=>'d4','C'=>'e4','D'=>'f4','E'=>'g4','F'=>'h4','G'=>'a5','H'=>'b5','I'=>'c5','J'=>'d5','K'=>'e5','L'=>'f5','M'=>'g5','N'=>'h5','O'=>'a6','P'=>'b6','Q'=>'c6','R'=>'d6','S'=>'e6','T'=>'f6','U'=>'g6','V'=>'h6','W'=>'a7','X'=>'b7','Y'=>'c7','Z'=>'d7','0'=>'e7','1'=>'f7','2'=>'g7','3'=>'h7','4'=>'a8','5'=>'b8','6'=>'c8','7'=>'d8','8'=>'e8','9'=>'f8','!'=>'g8','?'=>'h8');

    private static $rtoip = array('a1'=>'a','b1'=>'b','c1'=>'c','d1'=>'d','e1'=>'e','f1'=>'f','g1'=>'g','h1'=>'h','a2'=>'i','b2'=>'j','c2'=>'k','d2'=>'l','e2'=>'m','f2'=>'n','g2'=>'o','h2'=>'p','a3'=>'q','b3'=>'r','c3'=>'s','d3'=>'t','e3'=>'u','f3'=>'v','g3'=>'w','h3'=>'x','a4'=>'y','b4'=>'z','c4'=>'A','d4'=>'B','e4'=>'C','f4'=>'D','g4'=>'E','h4'=>'F','a5'=>'G','b5'=>'H','c5'=>'I','d5'=>'J','e5'=>'K','f5'=>'L','g5'=>'M','h5'=>'N','a6'=>'O','b6'=>'P','c6'=>'Q','d6'=>'R','e6'=>'S','f6'=>'T','g6'=>'U','h6'=>'V','a7'=>'W','b7'=>'X','c7'=>'Y','d7'=>'Z','e7'=>'0','f7'=>'1','g7'=>'2','h7'=>'3','a8'=>'4','b8'=>'5','c8'=>'6','d8'=>'7','e8'=>'8','f8'=>'9','g8'=>'!','h8'=>'?');
}
