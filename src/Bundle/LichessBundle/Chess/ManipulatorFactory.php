<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Stack;

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

    public function create(Game $game, Stack $stack = null)
    {
        $class = $this->class;
        $stack = $stack ?: new Stack();

        return new $class($game, $this->autodraw, $this->analyserFactory->create($game->getBoard()), $stack);
    }
}
