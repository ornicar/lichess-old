<?php

namespace Bundle\LichessBundle\Cheat;

use Bundle\LichessBundle\Document\GameRepository;
use Bundle\LichessBundle\Document\Game;
use Application\FOS\UserBundle\Document\User;

class Punisher
{
    protected $gameRepository;
    protected $logger;

    public function __construct(GameRepository $gameRepository)
    {
        $this->gameRepository = $gameRepository;
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
    }

    public function punish(User $user)
    {
        $games = $this->gameRepository->findCancelableByUser($user);
        foreach($games as $game) {
            $loser = $game->getLoser();
            if($eloDiff = $loser->getEloDiff()) {
                if($loserUser = $loser->getUser()) {
                    $this->log(sprintf('Restitute %d elo to %s for game %s', -$eloDiff, $loserUser->getUsername(), $game->getId()));
                    $loserUser->addElo(- $eloDiff);
                    $game->setIsEloCanceled(true);
                }
            }
        }
        $this->log(sprintf('Reset %s elo to %d', $user->getUsername(), User::STARTING_ELO));
        $user->setElo(User::STARTING_ELO);
    }

    protected function log($message)
    {
        if($this->logger) {
            $logger = $this->logger;
            $logger($message);
        }
    }
}
