<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Model\Game;
use Bundle\LichessBundle\Model\Player;
use Bundle\LichessBundle\Elo\Calculator;

class Finisher
{
    protected $calculator;
    protected $messenger;

    public function __construct(Calculator $calculator, Messenger $messenger)
    {
        $this->calculator = $calculator;
        $this->messenger = $messenger;
    }

    public function finish(Game $game)
    {
        $this->messenger->addSystemMessage($game, $game->getStatusMessage());
        $this->updateElo($game);
    }

    protected function updateElo(Game $game)
    {
        // Game can be aborted
        if(!$game->getIsFinished()) {
            return;
        }
        // Don't rate games with less than 2 moves
        if($game->getTurns() < 2) {
            return;
        }
        $white = $game->getPlayer('white');
        $black = $game->getPlayer('black');
        $whiteUser = $white->getUser();
        $blackUser = $black->getUser();
        // Don't rate games when one ore more player is not registered
        if(!$whiteUser || !$blackUser) {
            return;
        }
        if($winner = $game->getWinner()) {
            $win = $winner->isWhite() ? -1 : 1;
        } else {
            $win = 0;
        }
        list($whiteElo, $blackElo) = $this->calculator->calculate($whiteUser->getElo(), $blackUser->getElo(), $win);
        $whiteUser->setElo($whiteElo);
        $blackUser->setElo($blackElo);
    }
}
