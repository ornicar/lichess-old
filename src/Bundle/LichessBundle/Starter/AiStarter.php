<?php

namespace Bundle\LichessBundle\Starter;

use Bundle\LichessBundle\Ai\AiInterface;
use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Stack;
use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Config\GameConfig;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\ManipulatorFactory;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\Session;

class AiStarter implements StarterInterface
{
    protected $generator;
    protected $playerBlamer;
    protected $ai;
    protected $objectManager;
    protected $logger;
    protected $session;

    public function __construct(Generator $generator, PlayerBlamer $playerBlamer, AiInterface $ai, DocumentManager $objectManager, Logger $logger, ManipulatorFactory $manipulatorFactory, Session $session = null)
    {
        $this->generator          = $generator;
        $this->playerBlamer       = $playerBlamer;
        $this->ai                 = $ai;
        $this->objectManager      = $objectManager;
        $this->logger             = $logger;
        $this->manipulatorFactory = $manipulatorFactory;
        $this->session            = $session;
    }

    public function start(GameConfig $config)
    {
        if($this->session) {
            $this->session->set('lichess.game_config.ai', $config->toArray());
        }
        $color = $config->resolveColor();
        $player = $this->generator->createGameForPlayer($color, $config->variant);
        $this->playerBlamer->blame($player);
        $game = $player->getGame();
        $opponent = $player->getOpponent();
        $opponent->setIsAi(true);
        $opponent->setAiLevel(1);
        $game->start();

        if($player->isBlack()) {
            $this->manipulatorFactory->create($game)->play($this->ai->move($game, $opponent->getAiLevel()));
        }
        $this->objectManager->persist($game);
        $this->objectManager->flush(array('safe' => true));
        $this->logger->notice($game, 'Game:inviteAi create');

        return $player;
    }
}
