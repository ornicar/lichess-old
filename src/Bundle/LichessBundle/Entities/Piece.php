<?php

namespace Bundle\LichessBundle\Entities;

use Bundle\LichessBundle\Chess\Square;
use Bundle\LichessBundle\Chess\PieceFilter;
use Bundle\LichessBundle\Chess\MoveFilter;

abstract class Piece
{
    /**
     * the player that owns the piece
     *
     * @var Player
     */
    protected $player = null;

    /**
     * X position
     *
     * @var int
     */
    protected $x = null;

    /**
     * Y position
     *
     * @var int
     */
    protected $y = null;

    /**
     * Whether the piece is dead or not
     *
     * @var boolean
     */
    protected $isDead = false;

    /**
     * When this piece moved for the first time (usefull for en passant)
     *
     * @var int
     */
    protected $firstMove = null;

    /**
     * Unique hash
     *
     * @var string
     */
    protected $hash = null;
    
    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
        $this->hash = substr(\sha1(\uniqid().\mt_rand().microtime(true)), 0, 6);
    }

    /**
     * @return string
     */
    public function getHash()
    {
      return $this->hash;
    }

    /**
     * @return array
     */
    abstract public function getBasicTargetSquares();

    /**
     * @return string
     */
    abstract public function getClass();

    /**
     * @return integer
     */
    public function getFirstMove()
    {
        return $this->firstMove;
    }

    /**
     * @param integer
     */
    public function setFirstMove($firstMove)
    {
        $this->firstMove = $firstMove;
    }

    /**
     * @return boolean
     */
    public function getIsDead()
    {
        return $this->isDead;
    }

    /**
     * @param boolean
     */
    public function setIsDead($isDead)
    {
        $this->isDead = $isDead;
    }

    /**
     * @return boolean
     */
    public function isClass($class)
    {
        return $this->getClass() === $class;
    }

    /**
     * @return integer
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @param integer
     */
    public function setY($y)
    {
        $this->y = $y;
    }

    /**
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @param int
     */
    public function setX($x)
    {
        $this->x = $x;
    }

    /**
     * @return Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * @param Player
     */
    public function setPlayer($player)
    {
        $this->player = $player;
    }

    protected function getTargetsByProjection($x, $y)
    {
        $squares = array();
        $continue = true;

        $square = $this->getSquare();

        while($continue)
        {
            if ($square = $square->getSquareByRelativePos($x, $y))
            {
                if ($otherPiece = $square->getPiece())
                {
                    if ($otherPiece->getPlayer() !== $this->player)
                    {
                        $squares[] = $square;
                    }

                    $continue = false;
                }
                else
                {
                    $squares[] = $square;
                }
            }
            else
            {
                $continue = false;
            }
        }

        return $squares;
    }

    public function canMoveToSquare(Square $square)
    {
        return in_array($square->getKey(), $this->getTargetKeys());
    }

    public function getSquare()
    {
        return $this->getBoard()->getSquareByKey($this->getSquareKey());
    }

    public function getGame()
    {
        return $this->player->getGame();
    }

    public function getBoard()
    {
        return $this->getGame()->getBoard();
    }

    public function getSquareKey()
    {
        static $xKeys = array(1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd', 5 => 'e', 6 => 'f', 7 => 'g', 8 => 'h');

        return $xKeys[$this->x].$this->y;
    }

    public function toDebug()
    {
        $pos = ($square = $this->getSquare()) ? $square->getKey() : 'no-pos';

        return $this->getClass().' '.$this->getPlayer()->getColor().' in '.$pos;
    }

    public function __toString()
    {
        return $this->toDebug();
    }

    public function getColor()
    {
        return $this->player->getColor();
    }

    public function hasMoved()
    {
        return null !== $this->firstMove;
    }

    public function getForsythe()
    {
        $class = $this->getClass();

        if ('Knight' === $class)
        {
            $notation = 'N';
        }
        else
        {
            $notation = $class{0};
        }

        if('black' === $this->getColor())
        {
            $notation = strtolower($notation);
        }

        return $notation;
    }

    public function serialize()
    {
        return array('hash', 'color', 'x', 'y', 'player', 'isDead', 'firstMove');
    }

}
