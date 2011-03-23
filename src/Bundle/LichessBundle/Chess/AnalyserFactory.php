<?php

namespace Bundle\LichessBundle\Chess;

class AnalyserFactory
{
    protected $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function create(Board $board)
    {
        $class = $this->class;
        return new $class($board);
    }
}
