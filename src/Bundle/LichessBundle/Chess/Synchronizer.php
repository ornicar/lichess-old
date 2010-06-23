<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Entities\Player;
use Bundle\LichessBundle\Entities\Game;

class Synchronizer
{
    /**
    * If a player doesn't synchronize during this amount of seconds,
    * he is disconnected and resigns automatically
     *
     * @var int
     */
    protected $timeout = null;
    
    public function __construct($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Get timeout
     * @return int
     */
    public function getTimeout()
    {
      return $this->timeout;
    }
    
    /**
     * Set timeout
     * @param  int
     * @return null
     */
    public function setTimeout($timeout)
    {
      $this->timeout = $timeout;
    }
    
    /**
     * Synchronize the player game
     *
     * @return null
     **/
    public function synchronize(Player $player)
    {
        $this->update($player);
        if($player->getOpponent()->getTime() < (time() - $this->getTimeout())) {
            $player->getGame()->setStatus(Game::TIMEOUT);
            $player->setIsWinner(true);
        }
    }

    public function update(Player $player)
    {
        $player->setTime(time());
    }
}
