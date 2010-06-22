<?php

namespace Bundle\LichessBundle;
use Bundle\LichessBundle\Entities\Game;

abstract class Ai
{

    /**
     * Ai level
     *
     * @var int
     */
    protected $level = 1;
    
    public function __construct($level)
    {
        $this->level = 1;
    }

    abstract public function move(Game $game);
    
    /**
     * @return int
     */
    public function getLevel()
    {
      return $this->level;
    }
    
    /**
     * @param  int
     * @return null
     */
    public function setLevel($level)
    {
      $this->level = $level;
    }
}
