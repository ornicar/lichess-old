<?php

namespace Bundle\LichessBundle\Cheat;

use Bundle\LichessBundle\Document\GameRepository;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Elo\Updater;
use Application\UserBundle\Document\User;

class Punisher
{
    protected $gameRepository;
    protected $eloUpdater;
    protected $logger;

    public function __construct(GameRepository $gameRepository, Updater $eloUpdater)
    {
        $this->gameRepository = $gameRepository;
        $this->eloUpdater     = $eloUpdater;
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
    }

    public function punish(User $user)
    {
        $this->log(sprintf('Punish player %s', $user->getUsernameWithElo()));
        $games = $this->gameRepository->findCancelableByUser($user);
        foreach($games as $game) {
            $loser = $game->getLoser();
            if($eloDiff = $loser->getEloDiff()) {
                if($loserUser = $loser->getUser()) {
                    $this->log(sprintf('Restitute %d elo to %s for game %s', -$eloDiff, $loserUser->getUsername(), $game->getId()));
                    $this->eloUpdater->adjustElo($loserUser, $loserUser->getElo() - $eloDiff);
                    $game->setIsEloCanceled(true);
                }
            }
        }
        $this->log(sprintf('Reset %s elo to %d', $user->getUsername(), User::STARTING_ELO));
        $this->eloUpdater->adjustElo($user, User::STARTING_ELO);
    }

    protected function log($message)
    {
        if($this->logger) {
            $logger = $this->logger;
            $logger($message);
        }
    }
}
