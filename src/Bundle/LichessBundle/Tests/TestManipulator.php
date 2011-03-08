<?php

namespace Bundle\LichessBundle\Tests;

use Bundle\LichessBundle\Chess\Manipulator;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Stack;
use Bundle\LichessBundle\Chess\Autodraw;

class TestManipulator extends Manipulator
{
    public function __construct(Game $game, Stack $stack = null)
    {
        $autodraw = new Autodraw();
        $stack = $stack ?: new Stack();
        parent::__construct($game, $autodraw, $stack);
    }
}
