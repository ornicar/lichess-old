<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Logger;
use LogicException;

class Drawer
{
    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger    = $logger;
    }

    /**
     * The player declines the opponent draw offer
     */
    public function decline(Player $player)
    {
        $game = $player->getGame();
        if($player->getOpponent()->getIsOfferingDraw()) {
            $player->getOpponent()->setIsOfferingDraw(false);

            return 'Draw offer declined';
        } else {
            $this->logger->warn($player, 'Player:declineDrawOffer no offered draw');
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
            return 'Draw offer canceled';
        } else {
            $this->logger->warn($player, 'Player:cancelDrawOffer no offered draw');
        }

        return false;
    }
}
