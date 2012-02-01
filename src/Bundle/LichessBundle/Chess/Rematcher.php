<?php

namespace Bundle\LichessBundle\Chess;

use LogicException;
use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Chess\Messenger;
use Bundle\LichessBundle\Chess\Generator as GameGenerator;
use Bundle\LichessBundle\Sync\Memory;
use Bundle\LichessBundle\Document\Player;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Lichess\OpeningBundle\Starter\GameStarter;

class Rematcher
{
    protected $starter;
    protected $logger;
    protected $messenger;
    protected $gameGenerator;
    protected $memory;
    protected $urlGenerator;
    protected $objectManager;

    public function __construct(GameStarter $starter, Logger $logger, Messenger $messenger, Generator $generator, Memory $memory, UrlGeneratorInterface $router, DocumentManager $objectManager)
    {
        $this->starter        = $starter;
        $this->logger        = $logger;
        $this->messenger     = $messenger;
        $this->gameGenerator = $generator;
        $this->memory  = $memory;
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
        // if the rematch is already accepted
        // can happen if the guy clicks frenetically
        // on the rematch accept button
        if ($nextGame = $game->getNext()) {
            $nextOpponent = $nextGame->getPlayer($player->getColor());
            $nextPlayer   = $nextOpponent->getOpponent();
        } else {
            $nextOpponent = $this->gameGenerator->createReturnGame($opponent);
            $nextPlayer   = $nextOpponent->getOpponent();
            $nextGame     = $nextOpponent->getGame();
            $this->logger->notice($player, 'Player:rematch accept');
            $this->messenger->addSystemMessage($game, 'Rematch offer accepted');
            $this->starter->start($nextGame);
            $this->memory->setAlive($nextPlayer);
            // the opponent still pings the old game,
            // so we set it as active on the new game
            if ($this->memory->getActivity($opponent)) {
                $this->memory->setAlive($nextOpponent);
            }
        }
        // tell spectators to reload the table
        $game->addEventToStacks(array('type' => 'reload_table'));
        // tell players to move to next game
        foreach(array(array($player, $nextPlayer), array($opponent, $nextOpponent)) as $pair) {
            $pair[0]->addEventToStack(array('type' => 'redirect', 'url' => $this->urlGenerator->generate('lichess_player', array('id' => $pair[1]->getFullId()))));
        }
        $this->objectManager->persist($nextGame);

        return $nextGame;
    }
}
