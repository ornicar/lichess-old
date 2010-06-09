<?php

namespace Bundle\LichessBundle\Ai;

use Bundle\LichessBundle\Ai;
use Bundle\LichessBundle\Chess\Analyser;

class Stupid extends Ai
{

    public function move()
    {
        $analyser = new Analyser($this->player->getGame()->getBoard());
        $moveTree = $analyser->getPlayerPossibleMoves($this->player);

        // choose random piece
        do
    {
        $from = array_rand($moveTree);
    }
        while(empty($moveTree[$from]));

        // choose random move
        $to = $moveTree[$from][array_rand($moveTree[$from])];

        return $from.' '.$to;
    }
}
