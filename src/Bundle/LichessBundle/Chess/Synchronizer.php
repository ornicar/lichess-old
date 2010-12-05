<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Model\Player;
use Symfony\Component\DependencyInjection\ContainerAware;

class Synchronizer extends ContainerAware
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
        $storage = $this->container->get('lichess_storage');

        $nb = $storage->get('lichess.nb_players');
        if(false === $nb) {
            $it = $storage->getIterator('/alive$/');
            $nb = 0;
            $limit = time() - $this->getTimeout();
            foreach($it as $i) {
                $storage->get($i['key']); // clear invalidated entries
                if($i['mtime'] >= $limit) ++$nb;
            }
            $storage->store('lichess.nb_players', $nb, 2);
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
        $events = $playerStack->getEventsSince($clientVersion+1);

        return $events;
    }

    public function setAlive(Player $player)
    {
        $this->container->get('lichess_storage')->store($player->getGame()->getId().'.'.$player->getColor().'.alive', 1, $this->getTimeout());
    }

    public function isTimeout(Player $player)
    {
        return !$this->isConnected($player);
    }

    public function isConnected(Player $player)
    {
        return $player->getIsAi() || (bool) $this->container->get('lichess_storage')->get($player->getGame()->getId().'.'.$player->getColor().'.alive');
    }
}
