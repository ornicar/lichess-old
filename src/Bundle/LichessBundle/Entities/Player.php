<?php

namespace Bundle\LichessBundle\Entities;

/**
 * Represents a single Chess player for one game
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Player
{
    protected $hash;

    /**
     * the player color, white or black
     *
     * @var string
     */
    protected $color = null;
    
    /**
     * @return string
     */
    public function getColor()
    {
      return $this->color;
    }
    
    /**
     * @param string
     */
    public function setColor($color)
    {
      $this->color = $color;
    }

    /**
     * the player current game
     *
     * @var Game
     */
    protected $game = null;
    
    protected $pieces = array();

    public function __construct()
    {
        $this->hash = substr(\sha1(\uniqid().\mt_rand().microtime(true)), 0, 4);
    }

    public function setPieces(array $pieces)
    {
        $this->pieces = $pieces;
    }
    
    /**
     * @return Game
     */
    public function getGame()
    {
      return $this->game;
    }
    
    /**
     * @param Game
     */
    public function setGame($game)
    {
      $this->game = $game;
    }
}
