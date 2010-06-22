<?php

namespace Bundle\LichessBundle\Ai;

use Bundle\LichessBundle\Ai;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Entities\Game;

class Stupid extends Ai
{

    public function move(Game $game)
    {
        $analyser = new Analyser($game->getBoard());
        $moveTree = $analyser->getPlayerPossibleMoves($game->getTurnPlayer());

        // choose random piece
        do {
            $from = array_rand($moveTree);
        }
        while(empty($moveTree[$from]));

        // choose random move
        $to = $moveTree[$from][array_rand($moveTree[$from])];

        return $from.' '.$to;
    }
}
