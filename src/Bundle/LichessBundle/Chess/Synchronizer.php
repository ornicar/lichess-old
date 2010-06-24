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
        $game = $player->getGame();
        if($game->getIsStarted() && !$game->getIsFinished() && $this->isTimeout($player->getOpponent())) {
            $game->setStatus(Game::TIMEOUT);
            $player->setIsWinner(true);
        }
    }

    public function isTimeout(Player $player)
    {
        return !$player->getIsAi() && $player->getTime() < (time() - $this->getTimeout());
    }

    public function isConnected(Player $player)
    {
        return !$this->isTimeout($player);
    }

    public function update(Player $player)
    {
        $player->setTime(time());
    }
}
