<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Entities\Player;
use Bundle\LichessBundle\Entities\Game;

class Synchronizer
{
    /**
    * If a player doesn't synchronize during this amount of seconds,
    * he is disconnected and resigns automatically
     *
     * @var int
     */
    protected $timeout = null;
    /**
     * Apc prefix
     *
     * @var string
     */
    protected $apcPrefix = null;
    
    public function __construct($timeout, $apcPrefix)
    {
        $this->timeout = $timeout;
        $this->apcPrefix = $apcPrefix;
    }
    
    /**
     * Get apcPrefix
     * @return string
     */
    public function getApcPrefix()
    {
      return $this->apcPrefix;
    }
    
    /**
     * Set apcPrefix
     * @param  string
     * @return null
     */
    public function setApcPrefix($apcPrefix)
    {
      $this->apcPrefix = $apcPrefix;
    }

    /**
     * Get timeout
     * @return int
     */
    public function getTimeout()
    {
      return $this->timeout;
    }
    
    /**
     * Set timeout
     * @param  int
     * @return null
     */
    public function setTimeout($timeout)
    {
      $this->timeout = $timeout;
    }

    public function setAlive(Player $player)
    {
        $data = apc_fetch($this->getPlayer());
    }

    public function isTimeout(Player $player)
    {
        return !$this->isConnected($player);
    }

    public function isConnected(Player $player)
    {
        return $player->getIsAi() || !apc_exists($this->getPlayerApcKey($player));
    }

    protected function getPlayerApcKey(Player $player)
    {
        return $this->getApcPrefix().$player->getFullHash();
    }

    protected function getApcTimeOut()
    {
        return 5 * $this->getTimeout();
    }
}
