<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;
use APCIterator;

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
        $nb = apc_fetch('lichess.nb_players');
        if(false === $nb) {
            $it = new APCIterator('user', '/alive$/', APC_ITER_MTIME | APC_ITER_KEY, 100, APC_LIST_ACTIVE);
            $nb = 0;
            $limit = time() - $this->timeout;
            foreach($it as $i) {
                apc_fetch($i['key']); // clear invalidated entries
                if($i['mtime'] >= $limit) ++$nb;
            }
            apc_store('lichess.nb_players', $nb, 2);
        }

        return $nb;
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
        apc_store($this->getPlayerAliveKey($player), 1, $this->timeout);
    }

    public function isTimeout(Player $player)
    {
        return !$this->isConnected($player);
    }

    public function isConnected(Player $player)
    {
        return $player->getIsAi() || (bool) apc_fetch($this->getPlayerAliveKey($player));
    }

    protected function getPlayerAliveKey(Player $player)
    {
        return $player->getGame()->getId().'.'.$player->getColor().'.alive';
    }
}
