<?php

namespace Bundle\LichessBundle\Sync;

use Bundle\LichessBundle\Document\Player;
use Doctrine\ODM\MongoDB\DocumentManager;
use Bundle\LichessBundle\Provider;

class Synchronizer
{
    protected $dm;
    protected $provider;
    protected $memory;
    protected $httpPush;
    protected $clientUpdater;

    public function __construct(DocumentManager $dm, Provider $provider, Memory $memory, HttpPush $httpPush, ClientUpdater $clientUpdater)
    {
        $this->provider        = $provider;
        $this->dm        = $dm;
        $this->memory        = $memory;
        $this->httpPush      = $httpPush;
        $this->clientUpdater = $clientUpdater;
    }

    public function synchronize(Player $player, $version, $isSigned)
    {
        $game = $player->getGame();

        $player->cacheVersion();

        if ($this->httpPush->isSynced($player, $version)) {
            $this->httpPush->poll($player, $version);
            // only way to reload from DB
            $this->dm->clear();
            $player = $this->provider->findPublicPlayer($game->getId(), $player->getColor());
        }

        return $this->clientUpdater->getEventsSinceClientVersion($player, $version, $isSigned);
    }
}
