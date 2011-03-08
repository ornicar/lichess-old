<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Stack;

class ManipulatorFactory
{
    protected $class;

    public function __construct(Autodraw $autodraw, $class)
    {
        $this->autodraw = $autodraw;
        $this->class = $class;
    }

    public function create(Game $game, Stack $stack = null)
    {
        $class = $this->class;
        $stack = $stack ?: new Stack();

        return new $class($game, $this->autodraw, $stack);
    }
}
