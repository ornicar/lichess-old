<?php

namespace Bundle\LichessBundle\Sync;

use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;
use APCIterator;

class Memory
{
    /**
     * If a player doesn't synchronize during this amount of seconds,
     * he is disconnected and resigns automatically
     *
     * @var int
     */
    protected $hardTimeout;
    protected $softTimeout;

    public function __construct($softTimeout, $hardTimeout)
    {
        $this->softTimeout = (int) $softTimeout;
        $this->hardTimeout = (int) $hardTimeout;
    }

    public function getNbActivePlayers()
    {
        $nb = apc_fetch('lichess.nb_players');
        if(false === $nb) {
            $it = new APCIterator('user', '/alive$/', APC_ITER_MTIME | APC_ITER_KEY, 100, APC_LIST_ACTIVE);
            $nb = 0;
            $limit = time() - $this->hardTimeout;
            foreach($it as $i) {
                apc_fetch($i['key']); // clear invalidated entries
                if($i['mtime'] >= $limit) ++$nb;
            }
            apc_store('lichess.nb_players', $nb, 2);
        }

        return $nb;
    }

    public function setAlive(Player $player)
    {
        $this->setPlayerKeyAlive($this->getPlayerKey($player));
    }

    public function setPlayerKeyAlive($playerKey)
    {
        apc_store($playerKey, time(), $this->hardTimeout);
    }

    /**
     * Get player activity (or connectivity)
     * 2 - good connectivity
     * 1 - recently offline
     * 0 - offline for long time
     *
     * @param Player $player
     * @return int
     */
    public function getActivity(Player $player)
    {
        if ($player->getIsAi()) {
            return 2;
        }
        $latency = $this->getLatency($player);

        if ($latency <= $this->softTimeout) {
            return 2;
        }
        if ($latency <= $this->hardTimeout) {
            return 1;
        }
        return 0;
    }

    public function getVersion(Player $player)
    {
        return apc_fetch($player->getGame()->getId().'.'.$player->getColor().'.data');
    }

    public function setUsernameOnline($username)
    {
        apc_store('online.'.$username, true, $this->softTimeout);
    }

    protected function getLatency(Player $player)
    {
        $lastPlayerPing = apc_fetch($this->getPlayerKey($player));
        if (!$lastPlayerPing) {
            return 999999;
        }
        return time() - $lastPlayerPing;
    }

    public function getPlayerKey(Player $player)
    {
        return $player->getGame()->getId().'.'.$player->getColor().'.alive';
    }
}
