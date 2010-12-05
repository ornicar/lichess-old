<?php

namespace Bundle\LichessBundle\Ai;
use Bundle\LichessBundle\Model\Game;

interface AiInterface
{
    function move(Game $game, $level);
    function isAvailable();
}