<?php

namespace Lichess\OpeningBundle\Starter;

use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Logger;
use Lichess\OpeningBundle\Config\GameConfig;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Document\Clock;
use Lichess\OpeningBundle\Config\Persistence;

use Doctrine\ODM\MongoDB\DocumentManager;

class FriendStarter implements StarterInterface
{
    protected $generator;
    protected $playerBlamer;
    protected $objectManager;
    protected $logger;
    protected $configPersistence;

    public function __construct(Generator $generator, PlayerBlamer $playerBlamer, DocumentManager $objectManager, Logger $logger, Persistence $configPersistence)
    {
        $this->generator         = $generator;
        $this->playerBlamer      = $playerBlamer;
        $this->objectManager     = $objectManager;
        $this->logger            = $logger;
        $this->configPersistence = $configPersistence;
    }

    public function start(GameConfig $config)
    {
        $this->configPersistence->saveConfigFor('friend', $config->toArray());
        $color = $config->resolveColor();
        $player = $this->generator->createGameForPlayer($color, $config->getVariant());
        $this->playerBlamer->blame($player);
        $game = $player->getGame();
        if($config->getClock()) {
            $clock = new Clock($config->getTime() * 60, $config->getIncrement());
            $game->setClock($clock);
        }
        $game->setIsRated($config->getMode());
        $this->objectManager->persist($game);
        $this->logger->notice($game, 'Game:inviteFriend create');

        return $player;
    }
}
