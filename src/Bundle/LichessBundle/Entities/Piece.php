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
     * @var integer
     */
    protected $y = null;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
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
