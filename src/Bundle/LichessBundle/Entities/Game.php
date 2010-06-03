<?php

namespace Bundle\LichessBundle\Entities;

/**
 * Represents a single Chess game
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Game
{
    protected $isStarted;
    protected $isFinished;
    protected $turns;
    protected $players = array();
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

    public function setPlayers(array $players)
    {
        $this->players = $players;
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
    
}
