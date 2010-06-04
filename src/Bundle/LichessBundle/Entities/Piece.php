<?php

namespace Bundle\LichessBundle\Entities;

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
    protected $firstMove = 0;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

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
     * @return string
     */
    abstract public function getClass();

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
    
    
}
