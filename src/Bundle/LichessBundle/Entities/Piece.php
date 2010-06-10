<?php

namespace Bundle\LichessBundle\Entities;

use Bundle\LichessBundle\Chess\Square;
use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Chess\PieceFilter;

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

    /**
     * Performance pointer to the player game board
     * 
     * @var Board
     */
    protected $board = null;
    
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
    abstract public function getBasicTargetKeys();

    public function getAttackTargetKeys()
    {
        return $this->getBasicTargetKeys();
    }

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

    protected function getKeysByProjection($dx, $dy)
    {
        $keys = array();
        $continue = true;
        $x = $this->x;
        $y = $this->y;

        while($continue)
        {
            $x += $dx;
            $y += $dy;
            if($x>0 && $x<9 && $y>0 && $y<9)
            {
                $key = Board::posToKey($x, $y);
                if ($piece = $this->board->getPieceByKey($key))
                {
                    if ($piece->getPlayer() !== $this->player)
                    {
                        $keys[] = $key;
                    }

                    $continue = false;
                }
                else
                {
                    $keys[] = $key;
                }
            }
            else
            {
                $continue = false;
            }
        }

        return $keys;
    }

    public function getSquare()
    {
        return $this->board->getSquareByKey(Board::posToKey($this->x, $this->y));
    }

    public function getSquareKey()
    {
        return Board::posToKey($this->x, $this->y);
    }

    public function getGame()
    {
        return $this->player->getGame();
    }

    public function getBoard()
    {
        return $this->board;
    }

    public function setBoard(Board $board)
    {
        $this->board = $board;
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
