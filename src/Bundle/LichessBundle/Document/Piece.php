<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Chess\Board;

final class Piece
{
    /**
     * X position
     *
     * @var int
     */
    private $x = null;

    /**
     * Y position
     *
     * @var int
     */
    private $y = null;

    private $class;

    /**
     * Whether the piece is dead or not
     *
     * @var boolean
     */
    private $isDead = null;

    /**
     * Cache of the player color
     * This attribute is not persisted
     *
     * @var string
     */
    private $color = null;

    public function __construct($x, $y, $class)
    {
        $this->x = $x;
        $this->y = $y;
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass() {
        return $this->class;
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
        $this->isDead = $isDead ?: null;
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

    public function setColor($color)
    {
        $this->color = $color;
    }

    public function getSquareKey()
    {
        return Board::posToKey($this->x, $this->y);
    }

    public function getColor()
    {
        return $this->color;
    }

    public function __toString()
    {
        return $this->toDebug();
    }

    public function getForsyth()
    {
        $notation = $this->getPgn();

        if('black' === $this->getColor()) {
            $notation = strtolower($notation);
        }

        return $notation;
    }

    public function getPgn()
    {
        $class = $this->getClass();

        if ('Knight' === $class) {
            $notation = 'N';
        } else {
            $notation = $class{0};
        }

        return $notation;
    }

    public function getContextualHash()
    {
        $class = $this->getClass();

        return $class{0}.$this->color{0}.$this->x.$this->y;
    }

    public static function classToLetter($class)
    {
        return self::$ctl[$class];
    }

    public static function letterToClass($letter)
    {
        return self::$ltc[$letter];
    }

    private static $ctl = array('Pawn'=>'p','Bishop'=>'b','Knight'=>'n','Rook'=>'r','Queen'=>'q','King'=>'k');

    private static $ltc = array('p'=>'Pawn','b'=>'Bishop','n'=>'Knight','r'=>'Rook','q'=>'Queen','k'=>'King');
}
