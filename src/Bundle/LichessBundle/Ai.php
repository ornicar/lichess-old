<?php

namespace Bundle\LichessBundle;

use Bundle\LichessBundle\Entities\Player;

abstract class Ai
{
    /**
     * The Ai player
     *
     * @var Player
     */
    protected $player = null;

    /**
     * Ai options
     *
     * @var array
     */
    protected $options = array();
    
    public function __construct(Player $player, array $options = array())
    {
        $this->player = $player;
        $this->options = array_merge($this->options, $options);
    }

    abstract public function move();
    
    /**
     * Get options
     * @return array
     */
    public function getOptions()
    {
      return $this->options;
    }
    
    /**
     * Set options
     * @param  array
     * @return null
     */
    public function setOptions($options)
    {
      $this->options = $options;
    }
    
    /**
     * @return Player
     */
    public function getPlayer()
    {
      return $this->player;
    }
    
    /**
     * @param Player
     */
    public function setPlayer($player)
    {
      $this->player = $player;
    }
}
