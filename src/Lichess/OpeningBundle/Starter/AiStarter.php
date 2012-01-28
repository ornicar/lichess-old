<?php

namespace Lichess\OpeningBundle\Starter;

use Bundle\LichessBundle\Ai\AiInterface;
use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Stack;
use Bundle\LichessBundle\Logger;
use Lichess\OpeningBundle\Config\GameConfig;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\ManipulatorFactory;
use Lichess\OpeningBundle\Config\Persistence;

use Doctrine\ODM\MongoDB\DocumentManager;
use Lichess\OpeningBundle\Starter\GameStarter;

class AiStarter implements StarterInterface
{
    protected $starter;
    protected $generator;
    protected $playerBlamer;
    protected $ai;
    protected $objectManager;
    protected $logger;
    protected $configPersistence;

    public function __construct(GameStarter $starter, Generator $generator, PlayerBlamer $playerBlamer, AiInterface $ai, DocumentManager $objectManager, Logger $logger, ManipulatorFactory $manipulatorFactory, Persistence $configPersistence)
    {
        $this->starter          = $starter;
        $this->generator          = $generator;
        $this->playerBlamer       = $playerBlamer;
        $this->ai                 = $ai;
        $this->objectManager      = $objectManager;
        $this->logger             = $logger;
        $this->manipulatorFactory = $manipulatorFactory;
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

        if($player->isBlack()) {
            $this->manipulatorFactory->create($game)->play($this->ai->move($game, $opponent->getAiLevel()));
        }
        $game->compress();
        $this->objectManager->persist($game);
        $this->logger->notice($game, 'Game:inviteAi create');

        return $player;
    }
}
