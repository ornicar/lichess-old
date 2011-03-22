<?php

namespace Bundle\LichessBundle\Sync;

use Bundle\LichessBundle\Document\Player;

class Synchronizer
{
    protected $memory;
    protected $httpPush;
    protected $clientUpdater;

    public function __construct(Memory $memory, HttpPush $httpPush, ClientUpdater $clientUpdater)
    {
        $this->memory        = $memory;
        $this->httpPush      = $httpPush;
        $this->clientUpdater = $clientUpdater;
    }

    public function synchronize(Player $player, $version, $isSigned)
    {
        if($isSigned) {
            $this->memory->setAlive($player);
        }
        $player->getGame()->cachePlayerVersions();

        $this->httpPush->poll($player, $version);

        return $this->clientUpdater->getEventsSinceClientVersion($player, $version, $isSigned);
    }
}
