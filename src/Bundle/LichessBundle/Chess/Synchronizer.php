<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;

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

    public function getNbConnectedPlayers()
    {
        $cleanup = 0 === rand(0, 20);
        $it = new \APCIterator('user', '/alive$/', $cleanup ? APC_ITER_MTIME | APC_ITER_KEY : APC_ITER_MTIME, 100, APC_LIST_ACTIVE);
        $nb = 0;
        $limit = time() - $this->getTimeOut();
        foreach($it as $i) {
            if($cleanup) apc_fetch($i['key']);
            if($i['mtime'] >= $limit) ++$nb;
        }

        return $nb;
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
        $playerStack = $player->getStack();
        $stackVersion = $playerStack->getVersion();
        if($stackVersion === $clientVersion) {
            return array();
        }
        if(!$playerStack->hasVersion($clientVersion)) {
            throw new \OutOfBoundsException();
        }
        $events = array();
        for($version = $clientVersion+1; $version <= $stackVersion; $version++) {
            $events[] = $playerStack->getEvent($version);
        }

        return $events;
    }

    public function setAlive(Player $player)
    {
        apc_store($player->getGame()->getId().'.'.$player->getColor().'.alive', 1, $this->getTimeout());
    }

    public function isTimeout(Player $player)
    {
        return !$this->isConnected($player);
    }

    public function isConnected(Player $player)
    {
        return $player->getIsAi() || (bool) apc_fetch($player->getGame()->getId().'.'.$player->getColor().'.alive');
    }
}
