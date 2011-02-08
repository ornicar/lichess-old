<?php

namespace Bundle\LichessBundle\Ai;

use Bundle\LichessBundle\Document\Game;

interface AiInterface
{
    public function move(Game $game, $level);
}
