<?php

namespace Bundle\LichessBundle\Chess;

use LogicException;
use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Chess\Messenger;
use Bundle\LichessBundle\Chess\Generator as GameGenerator;
use Bundle\LichessBundle\Chess\Synchronizer;
use Bundle\LichessBundle\Document\Player;
use Symfony\Component\Routing\Router;
use Doctrine\ODM\MongoDB\DocumentManager;

class Rematcher
{
    protected $logger;
    protected $messenger;
    protected $gameGenerator;
    protected $synchronizer;
    protected $urlGenerator;
    protected $objectManager;

    public function __construct(Logger $logger, Messenger $messenger, Generator $generator, Synchronizer $synchronizer, Router $router, DocumentManager $objectManager)
    {
        $this->logger        = $logger;
        $this->messenger     = $messenger;
        $this->gameGenerator = $generator;
        $this->synchronizer  = $synchronizer;
        $this->urlGenerator  = $router->getGenerator();
        $this->objectManager = $objectManager;
    }

    public function rematch(Player $player)
    {
        if(!$player->canOfferRematch()) {
            throw new LogicException($this->logger->formatPlayer($player, 'Player:rematch'));
        } elseif($player->getOpponent()->getIsOfferingRematch()) {
            $this->acceptRematch($player);
        } else {
            $this->offerRematch($player);
        }
    }

    protected function offerRematch(Player $player)
    {
        $game = $player->getGame();
        $this->logger->notice($player, 'Player:rematch offer');
        $this->messenger->addSystemMessage($game, 'Rematch offer sent');
        $player->setIsOfferingRematch(true);
        $game->addEventToStacks(array('type' => 'reload_table'));
    }

    protected function acceptRematch(Player $player)
    {
        $game         = $player->getGame();
        $opponent     = $player->getOpponent();
        $nextOpponent = $this->gameGenerator->createReturnGame($opponent);
        $nextPlayer   = $nextOpponent->getOpponent();
        $nextGame     = $nextOpponent->getGame();
        $this->logger->notice($player, 'Player:rematch accept');
        $this->messenger->addSystemMessage($game, 'Rematch offer accepted');
        $nextGame->start();
        foreach(array(array($player, $nextPlayer), array($opponent, $nextOpponent)) as $pair) {
            $this->synchronizer->setAlive($pair[1]);
            $pair[0]->addEventToStack(array('type' => 'redirect', 'url' => $this->urlGenerator->generate('lichess_player', array('id' => $pair[1]->getFullId()))));
        }
        $this->objectManager->persist($nextGame);
    }
}
