<?php

namespace Bundle\LichessBundle\Elo;

use Bundle\LichessBundle\Document\HistoryRepository;
use Bundle\LichessBundle\Document\Game;
use Application\UserBundle\Document\User;

class Updater
{
    protected $historyRepository;

    public function __construct(HistoryRepository $historyRepository)
    {
        $this->historyRepository = $historyRepository;
    }

    public function updateElo(User $user, $elo, Game $game)
    {
        $user->setElo($elo);
        $ts = date_create()->getTimestamp();
        $this->historyRepository->findOneByUserOrCreate($user)->addGame($ts, $elo, $game->getId());
    }

    public function adjustElo(User $user, $elo)
    {
        $user->setElo($elo);
        $ts = date_create()->getTimestamp();
        $this->historyRepository->findOneByUserOrCreate($user)->addAdjust($ts, $elo);
    }
}
