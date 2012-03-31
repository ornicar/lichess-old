<?php

namespace Lichess\OpeningBundle\Starter;

use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Logger;
use Lichess\OpeningBundle\Config\GameConfig;
use Bundle\LichessBundle\Chess\Generator;
use Lichess\OpeningBundle\Config\Persistence;

use Doctrine\ODM\MongoDB\DocumentManager;
use Lichess\OpeningBundle\Starter\GameStarter;

class AiStarter implements StarterInterface
{
    protected $starter;
    protected $generator;
    protected $playerBlamer;
    protected $objectManager;
    protected $logger;
    protected $configPersistence;

    public function __construct(GameStarter $starter, Generator $generator, PlayerBlamer $playerBlamer, DocumentManager $objectManager, Logger $logger, Persistence $configPersistence)
    {
        $this->starter          = $starter;
        $this->generator          = $generator;
        $this->playerBlamer       = $playerBlamer;
        $this->objectManager      = $objectManager;
        $this->logger             = $logger;
        $this->configPersistence  = $configPersistence;
    }

    public function start(GameConfig $config)
    {
        $this->configPersistence->saveConfigFor('ai', $config->toArray());
        $color = $config->resolveColor();
        $player = $this->generator->createGameForPlayer($color, $config->getVariant());
        $this->playerBlamer->blame($player);
        $game = $player->getGame();
        $opponent = $player->getOpponent();
        $opponent->setIsAi(true);
        $opponent->setAiLevel($config->getLevel());
        $this->starter->start($game);

        $this->objectManager->persist($game);

        return $player;
    }
}
