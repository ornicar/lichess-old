<?php

namespace Bundle\LichessBundle\Chess;

use Symfony\Component\EventDispatcher\Event;

class GameEvent extends Event
{
    private $game;

    public function __construct($game)
    {
        $this->game = $game;
    }

    public function getGame()
    {
        return $this->game;
    }
}
