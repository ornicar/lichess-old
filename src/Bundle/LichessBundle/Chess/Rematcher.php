<?php

namespace Bundle\LichessBundle\Chess;

use LogicException;
use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Chess\Generator as GameGenerator;
use Bundle\LichessBundle\Document\Player;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Lichess\OpeningBundle\Starter\GameStarter;

class Rematcher
{
    protected $starter;
    protected $logger;
    protected $gameGenerator;
    protected $urlGenerator;
    protected $objectManager;

    public function __construct(GameStarter $starter, Logger $logger, Generator $generator, UrlGeneratorInterface $router, DocumentManager $objectManager)
    {
        $this->starter        = $starter;
        $this->logger        = $logger;
        $this->gameGenerator = $generator;
        $this->urlGenerator  = $router;
        $this->objectManager = $objectManager;
    }

    public function rematch(Player $player)
    {
        if(!$player->canOfferRematch()) {
            throw new LogicException($this->logger->formatPlayer($player, 'Player:rematch'));
        } elseif($player->getOpponent()->getIsOfferingRematch()) {
            return $this->acceptRematch($player);
        } elseif(!$player->getIsOfferingRematch()) {
            return $this->offerRematch($player);
        }
    }

    public function rematchCancel(Player $player)
    {
        if ($player->getIsOfferingRematch()) {
            $player->setIsOfferingRematch(false);
            return true;
        }
        return false;
    }

    protected function offerRematch(Player $player)
    {
        $player->setIsOfferingRematch(true);
    }

    protected function acceptRematch(Player $player)
    {
        $game         = $player->getGame();
        $opponent     = $player->getOpponent();
        // if the rematch is already accepted
        // can happen if the guy clicks frenetically
        // on the rematch accept button
        if ($nextGame = $game->getNext()) {
            $nextOpponent = $nextGame->getPlayer($player->getColor());
            $nextPlayer   = $nextOpponent->getOpponent();
            $messages = array();
        } else {
            $nextOpponent = $this->gameGenerator->createReturnGame($opponent);
            $nextPlayer   = $nextOpponent->getOpponent();
            $nextGame     = $nextOpponent->getGame();
            $messages = $this->starter->start($nextGame);
        }
        $this->objectManager->persist($nextGame);

        return array($nextGame, $messages);
    }
}
