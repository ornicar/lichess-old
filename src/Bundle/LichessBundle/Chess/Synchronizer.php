<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;

class Synchronizer
{
    protected $lowLevelSynchronizer;

    public function __construct($lowLevelSynchronizer)
    {
        $this->lowLevelSynchronizer = $lowLevelSynchronizer;
    }

    public function getNbActivePlayers()
    {
        return $this->lowLevelSynchronizer->getNbActivePlayers();
    }

    public function setAlive(Player $player)
    {
        $this->lowLevelSynchronizer->setAlive($player->getGame()->getId(), $player->getColor());
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
        return $this->lowLevelSynchronizer->getActivity($player->getGame()->getId(), $player->getColor());
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
}
