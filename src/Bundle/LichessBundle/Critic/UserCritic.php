<?php

namespace Bundle\LichessBundle\Critic;
use Bundle\DoctrineUserBundle\Document\User;
use Bundle\LichessBundle\Document\GameRepository;

class UserCritic
{
    protected $user;
    protected $gameRepository;

    public function __construct(GameRepository $gameRepository)
    {
        $this->gameRepository = $gameRepository;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getNbGames()
    {
        return $this->gameRepository->countByUser($this->user);
    }
}
