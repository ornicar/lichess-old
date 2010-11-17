<?php

namespace Bundle\LichessBundle\Critic;
use Bundle\DoctrineUserBundle\Document\User;
use Bundle\LichessBundle\Document\GameRepository;
use Bundle\LichessBundle\Document\Game;

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

    public function getNbWins()
    {
        return $this->gameRepository->createByUserQuery($this->user)
            ->where('winnerUserId')->equals(new \MongoId($this->user->getId()))
            ->count();
    }

    public function getNbDefeats()
    {
        return $this->gameRepository->createByUserQuery($this->user)
            ->where('winnerUserId')->exists(true)
            ->where('winnerUserId')->notEqual(new \MongoId($this->user->getId()))
            ->count();
    }
}
