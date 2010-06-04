<?php

namespace Bundle\LichessBundle\Entities;

/**
 * Represents a single Chess game
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Game
{
    /**
     * Whether the game has started or not
     *
     * @var boolean
     */
    protected $isStarted = false;
    
    /**
     * Whether the game is finished or not
     *
     * @var boolean
     */
    protected $isFinished = false;
    
    /**
     * The two players 
     * 
     * @var array
     */
    protected $players = array();

    /**
     * Number of turns passed
     *
     * @var integer
     */
    protected $turns = 0;
    
    /**
     * unique hash of the game
     *
     * @var string
     */
    protected $hash = null;
    
    public function __construct()
    {
        $this->hash = substr(\sha1(\uniqid().\mt_rand().microtime(true)), 0, 8);
    }
    
    /**
     * @return boolean
     */
    public function getIsFinished()
    {
      return $this->isFinished;
    }
    
    /**
     * @param boolean
     */
    public function setIsFinished($isFinished)
    {
      $this->isFinished = $isFinished;
    }
    
    
    /**
     * @return boolean
     */
    public function getIsStarted()
    {
      return $this->isStarted;
    }
    
    /**
     * @param boolean
     */
    public function setIsStarted($isStarted)
    {
      $this->isStarted = $isStarted;
    }
    

    public function setPlayers(array $players)
    {
        $this->players = $players;
    }

    public function getPlayers()
    {
      return $this->players;
    }

    /**
     * @return Player
     */
    public function getPlayer($color)
    {
      return $this->players[$color];
    }

    public function getWinner()
    {
      if($this->getPlayer('white')->getIsWinner()) {
        return $this->getPlayer('white');
      }
      elseif($this->getPlayer('black')->getIsWinner()) {
        return $this->getPlayer('black');
      }
    }

    public function setPlayer($color, $player)
    {
        $this->players[$color] = $player;
    }

    /**
     * @return string
     */
    public function getHash()
    {
      return $this->hash;
    }
    
    /**
     * @return integer
     */
    public function getTurns()
    {
      return $this->turns;
    }
    
    /**
     * @param integer
     */
    public function setTurns($turns)
    {
      $this->turns = $turns;
    }
    

    public function getPieces()
    {
      return array_merge($this->getPlayer('white')->getPieces(), $this->getPlayer('black')->getPieces());
    }

    public function __toString()
    {
      return '#'.$this->getId(). 'turn '.$this->getTurns();
    }
    
}
