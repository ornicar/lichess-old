<?php

namespace Bundle\LichessBundle\Chess\Synchronizer;

class Synchronizer
{

    /**
     * The player to synchronize
     *
     * @var Player
     */
    protected $player = null;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    /**
     * Synchronize the player game
     *
     * @return null
     **/
    public function synchronize($time)
    {
        $player->setTime($time);
        $game = $player->getGame();
        $opponent = $player->getOpponent();
        if($opponent->getTime() < ($time - $this->getTimeOut())) {
            $this->eliminate($opponent);
        }
    }

    protected function eliminate(Player $player)
    {
        $player->setIsTimeOut(true);
        $player->getGame()->setIsFinished(true);
        $player->getOpponent()->setIsWinner(true);
    }

    public function getTimeout()
    {
        return 5;
    }
    
    /**
     * Get player
     * @return Player
     */
    public function getPlayer()
    {
      return $this->player;
    }
    
    /**
     * Set player
     * @param  Player
     * @return null
     */
    public function setPlayer($player)
    {
      $this->player = $player;
    }
}
