<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Ai\AiInterface;
use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Logger;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\Session;

class Starter
{
    protected $generagor;
    protected $playerBlamer;
    protected $ai;
    protected $documentManager;
    protected $logger;
    protected $session;

    public function __construct(Generator $generator, PlayerBlamer $playerBlamer, AiInterface $ai, DocumentManager $documentManager, Logger $logger, Session $session = null)
    {
        $this->generator = $generator;
        $this->playerBlamer = $playerBlamer;
        $this->ai = $ai;
        $this->documentManager = $documentManager;
        $this->logger = $logger;
        $this->session = $session;
    }

    public function startAi(GameConfig $config, $color, AiInterface $ai)
    {
        if($this->session) {
            $this->session->set('lichess.game_config.ai', $config->toArray());
        }
        $player = $this->generator->createGameForPlayer($color, $config->variant);
        $this->playerBlamer->blame($player);
        $game = $player->getGame();
        $opponent = $player->getOpponent();
        $opponent->setIsAi(true);
        $opponent->setAiLevel(1);
        $game->start();

        if($player->isBlack()) {
            $manipulator = new Manipulator($game, new Stack());
            $manipulator->play($this->ai->move($game, $opponent->getAiLevel()));
        }
        $this->documentManager->persist($game);
        $this->documentManager->flush(array('safe' => true));
        $this->logger->notice($game, 'Game:inviteAi create');
    }
}
