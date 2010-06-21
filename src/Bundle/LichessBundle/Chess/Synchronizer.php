<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Entities\Player;
use Bundle\LichessBundle\Entities\Game;

class Synchronizer
{
    /**
     * Synchronize the player game
     *
     * @return null
     **/
    public function synchronize(Player $player)
    {
        $this->update($player);
        if($player->getOpponent()->getTime() < (time() - $this->getTimeLimit())) {
            $player->getGame()->setStatus(Game::TIMEOUT);
            $player->setIsWinner(true);
        }
    }

    public function update(Player $player)
    {
        $player->setTime(time());
    }

    public function getTimeLimit()
    {
        return 25;
    }
}
