<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Logger;
use LogicException;

class Drawer
{
    protected $messenger;
    protected $finisher;
    protected $logger;

    public function __construct(Messenger $messenger, Finisher $finisher, Logger $logger)
    {
        $this->messenger = $messenger;
        $this->finisher  = $finisher;
        $this->logger    = $logger;
    }

    /**
     * This players offers a draw
     */
    public function offer(Player $player)
    {
        $game = $player->getGame();
        if($game->getIsPlayable()) {
            if (!$game->getHasEnoughMovesToDraw()) {
                throw new LogicException('Too early to draw');
            }
            if(!$player->getIsOfferingDraw()) {
                if($player->getOpponent()->getIsOfferingDraw()) {
                    return $this->accept($player);
                }
                $player->setIsOfferingDraw(true);

                return $this->messenger->addSystemMessage($game, 'Draw offer sent');
            } else {
                $this->logger->warn($player, 'Player:offerDraw already offered');
            }
        } else {
            $this->logger->warn($player, 'Player:offerDraw on finished game');
        }

        return false;
    }

    /**
     * The player declines the opponent draw offer
     */
    public function decline(Player $player)
    {
        $game = $player->getGame();
        if($player->getOpponent()->getIsOfferingDraw()) {
            $player->getOpponent()->setIsOfferingDraw(false);

            return $this->messenger->addSystemMessage($game, 'Draw offer declined');
        } else {
            $this->logger->warn($player, 'Player:declineDrawOffer no offered draw');
        }

        return false;
    }

    /**
     * The player accepts the opponent draw offer
     */
    public function accept(Player $player)
    {
        $game = $player->getGame();
        if($player->getOpponent()->getIsOfferingDraw()) {
            $this->finisher->finish($game, Game::DRAW, null);

            return $this->messenger->addSystemMessage($game, 'Draw offer accepted');
        } else {
            $this->logger->warn($player, 'Player:acceptDrawOffer no offered draw');
        }

        return false;
    }

    /**
     * The player cancels his previous draw offer
     */
    public function cancel(Player $player)
    {
        $game = $player->getGame();
        if($player->getIsOfferingDraw()) {
            $player->setIsOfferingDraw(false);
            return $this->messenger->addSystemMessage($game, 'Draw offer canceled');
        } else {
            $this->logger->warn($player, 'Player:cancelDrawOffer no offered draw');
        }

        return false;
    }
}
