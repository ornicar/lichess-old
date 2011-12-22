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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Doctrine\ODM\MongoDB\DocumentManager;
use Bundle\LichessBundle\Chess\GameEvent;

class AiStarter implements StarterInterface
{
    protected $generator;
    protected $playerBlamer;
    protected $ai;
    protected $objectManager;
    protected $logger;
    protected $configPersistence;

    public function __construct(Generator $generator, PlayerBlamer $playerBlamer, AiInterface $ai, DocumentManager $objectManager, Logger $logger, ManipulatorFactory $manipulatorFactory, Persistence $configPersistence, EventDispatcherInterface $dispatcher)
    {
        $this->generator          = $generator;
        $this->playerBlamer       = $playerBlamer;
        $this->ai                 = $ai;
        $this->objectManager      = $objectManager;
        $this->logger             = $logger;
        $this->manipulatorFactory = $manipulatorFactory;
        $this->configPersistence  = $configPersistence;
        $this->dispatcher   = $dispatcher;
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
        $game->start();

        if($player->isBlack()) {
            $this->manipulatorFactory->create($game)->play($this->ai->move($game, $opponent->getAiLevel()));
        }
        $this->objectManager->persist($game);
        $this->logger->notice($game, 'Game:inviteAi create');

		if ($game->hasUser()) {
			$event = new GameEvent($game);
			$this->dispatcher->dispatch('lichess_game.start', $event);
		}

        return $player;
    }
}
