<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Chess\PieceFilter;

/**
 * @mongodb:MappedSuperclass
 */
abstract class Piece
{
    /**
     * X position
     *
     * @var int
     * @mongodb:Field(type="int")
     */
    protected $x = null;

    /**
     * Y position
     *
     * @var int
     * @mongodb:Field(type="int")
     */
    protected $y = null;

    /**
     * Whether the piece is dead or not
     *
     * @var boolean
     * @mongodb:Field(type="boolean", name="d")
     */
    protected $isDead = null;

    /**
     * When this piece moved for the first time (useful for en passant)
     *
     * @var int
     * @mongodb:Field(type="int", name="f")
     */
    protected $firstMove = null;

    /**
     * the player that owns the piece
     *
     * @var Player
     */
    protected $player = null;

    /**
     * Performance pointer to the player game board
     *
     * @var Board
     */
    protected $board = null;

    /**
     * Cache of the player color
     * This attribute is not persisted
     *
     * @var string
     */
    protected $color = null;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
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
        return (boolean) $this->isDead;
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
        $this->color = $player->getColor();
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
                    if ($piece->getColor() !== $this->color)
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

    public function getColor()
    {
        return $this->color;
    }

    public function hasMoved()
    {
        return null !== $this->firstMove;
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

    public function getForsyth()
    {
        $notation = $this->getPgn();

        if('black' === $this->getColor())
        {
            $notation = strtolower($notation);
        }

        return $notation;
    }

    public function getPgn()
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

        return $notation;
    }

    public function getContextualHash()
    {
        $class = $this->getClass();
        return $class{0}.$this->color{0}.$this->x.$this->y;
    }
}
