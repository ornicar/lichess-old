<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Game;
use ArrayObject;

class ManipulatorFactory
{
    protected $autodraw;
    protected $analyserFactory;
    protected $class;

    public function __construct(Autodraw $autodraw, AnalyserFactory $analyserFactory, $class)
    {
        $this->autodraw = $autodraw;
        $this->analyserFactory = $analyserFactory;
        $this->class = $class;
    }

    public function create(Game $game, ArrayObject $events = null)
    {
        $class = $this->class;
        $events = $events ?: new ArrayObject();

        return new $class($game, $this->autodraw, $this->analyserFactory->create($game->getBoard()), $events);
    }
}
