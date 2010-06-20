<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Entities\Player;

class Synchronizer
{

    /**
     * Synchronize the player game
     *
     * @return null
     **/
    public function synchronize(Player $player)
    {
        $time = time();
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
}
