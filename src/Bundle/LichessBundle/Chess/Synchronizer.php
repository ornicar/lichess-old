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
        $time = time();
        $player->setTime($time);
        $game = $player->getGame();
        $opponent = $player->getOpponent();
        if($opponent->getTime() < ($time - $this->getTimeLimit())) {
            $game->setStatus(Game::TIMEOUT);
            $player->setIsWinner(true);
        }
    }

    public function getTimeLimit()
    {
        return 12;
    }
}
