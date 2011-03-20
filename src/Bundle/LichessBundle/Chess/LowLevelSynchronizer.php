<?php

namespace Bundle\LichessBundle\Chess;

use APCIterator;

class LowLevelSynchronizer
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
        $this->softTimeout = $softTimeout;
        $this->hardTimeout = $hardTimeout;
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

    public function setAlive($gameId, $color)
    {
        apc_store($this->getAliveKey($gameId, $color), time(), $this->hardTimeout);
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
    public function getActivity($gameId, $color)
    {
        $latency = $this->getLatency($gameId, $color);

        if ($latency <= $this->softTimeout) {
            return 2;
        }
        if ($latency <= $this->hardTimeout) {
            return 1;
        }
        return 0;
    }

    public function getVersion($gameId, $color)
    {
        return apc_fetch($gameId.'.'.$color.'.data');
    }

    public function setUsernameOnline($username)
    {
        apc_store('online.'.$username, true, $this->softTimeout);
    }

    protected function getLatency($gameId, $color)
    {
        $lastPlayerPing = apc_fetch($this->getAliveKey($gameId, $color));
        if (!$lastPlayerPing) {
            return 999999;
        }
        return time() - $lastPlayerPing;
    }

    protected function getAliveKey($gameId, $color)
    {
        return $gameId.'.'.$color.'.alive';
    }
}
