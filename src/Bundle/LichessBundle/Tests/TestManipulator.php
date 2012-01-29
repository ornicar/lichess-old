<?php

namespace Bundle\LichessBundle\Tests;

use Bundle\LichessBundle\Chess\Manipulator;

use Bundle\LichessBundle\Document\Game;
use ArrayObject;
use Bundle\LichessBundle\Chess\Autodraw;
use Bundle\LichessBundle\Chess\Analyser;

class TestManipulator extends Manipulator
{
    public function __construct(Game $game, ArrayObject $events = null)
    {
        $autodraw = new Autodraw();
        $events = $events ?: new ArrayObject();
        $analyser = new Analyser($game->getBoard());
        parent::__construct($game, $autodraw, $analyser, $events);
    }
}
