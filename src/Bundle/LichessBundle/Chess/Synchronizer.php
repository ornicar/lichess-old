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

    public function __construct($timeout)
    {
        $this->timeout = $timeout;
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
        $this->timeout = (int) $timeout;
    }

    public function getDiffEvents(Player $player, $clientVersion)
    {
        $stackVersion = $player->getStack()->getVersion();
        if($stackVersion == $clientVersion) {
            return array();
        }
        $events = array();
        for($version = $clientVersion+1; $version <= $stackVersion; $version++) {
            $events[] = $player->getStack()->getEvent($version);
        }

        return $events;
    }

    public function setAlive(Player $player)
    {
        apc_store($player->getGame()->getHash().'.'.$player->getColor().'.alive', 1, $this->getTimeout());
    }

    public function isTimeout(Player $player)
    {
        return !$this->isConnected($player);
    }

    public function isConnected(Player $player)
    {
        return $player->getIsAi() || (bool) apc_fetch($player->getGame()->getHash().'.'.$player->getColor().'.alive');
    }
}
