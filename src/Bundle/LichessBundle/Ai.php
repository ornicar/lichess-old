<?php

namespace Bundle\LichessBundle;

use Bundle\LichessBundle\Entities\Player;

abstract class Ai
{
    /**
     * The Ai player
     *
     * @var Player
     */
    protected $player = null;
    
    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    abstract public function move();
    
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
