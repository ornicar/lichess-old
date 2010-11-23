<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Elo\Calculator;

class Finisher
{
    protected $calculator;

    public function __construct(Calculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function finish(Game $game)
    {
        if($game->getTurns() >= 2) {
            $white = $game->getPlayer('white')->getUser();
            $black = $game->getPlayer('black')->getUser();
            if($white && $black) {
                if($winner = $game->getWinner()) {
                    $win = $winner->isWhite() ? -1 : 1;
                } else {
                    $win = 0;
                }
                list($whiteElo, $blackElo) = $this->calculator->calculate($white->getElo(), $black->getElo(), $win);
                $white->setElo($whiteElo);
                $black->setElo($blackElo);
            }
        }
    }
}
