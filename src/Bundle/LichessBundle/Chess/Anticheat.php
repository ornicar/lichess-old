<?php

namespace Bundle\LichessBundle\Chess;
use Bundle\LichessBundle\Model\Game;
use Bundle\LichessBundle\Model\GameRepository;

class Anticheat
{
    protected $gameRepository;
    protected $turns;

    public function __construct(GameRepository $gameRepository, $turns)
    {
        $this->gameRepository = $gameRepository;
        $this->turns = $turns;
    }

    public function detectCheater(Game $game)
    {
        if($game->getTurns() != $this->turns && $game->getTurns() != ($this->turns+1)) {
            return false;
        }
        if($game->getInvited()->getIsAi()) {
            return false;
        }

        // Detect client using AI
        $similarGames = $this->gameRepository->findSimilar($game, new \DateTime('-10 minutes'));
        foreach($similarGames as $similarGame) {
            if($similarGame->getInvited()->getIsAi()) {
                return $game->getPlayer($similarGame->getInvited()->getColor());
            }
        }

        return false;
    }
}
