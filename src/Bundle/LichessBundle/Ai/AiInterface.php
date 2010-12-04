<?php

namespace Bundle\LichessBundle\Ai;
use Bundle\LichessBundle\Document\Game;

interface AiInterface
{
    function move(Game $game, $level);
    function isAvailable();
}