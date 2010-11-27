<?php

namespace Bundle\LichessBundle\Critic;
use Bundle\DoctrineUserBundle\Document\User;
use Bundle\LichessBundle\Document\GameRepository;
use Bundle\LichessBundle\Document\Game;
use Application\DoctrineUserBundle\Document\UserRepository;

class UserCritic
{
    protected $user;
    protected $gameRepository;
    protected $userRepository;
    protected $cache;

    public function __construct(GameRepository $gameRepository, UserRepository $userRepository)
    {
        $this->gameRepository = $gameRepository;
        $this->userRepository = $userRepository;
    }

    public function setUser(User $user)
    {
        $this->cache = array();
        $this->user = $user;
    }

    public function getRank()
    {
        return $this->cacheable('rank', function($games, $users, $user) {
            return $users->getUserRank($user);
        });
    }

    public function getNbUsers()
    {
        return $this->cacheable('nbUsers', function($games, $users, $user) {
            return $users->createQueryBuilder()->count();
        });
    }

    public function getNbGames()
    {
        return $this->cacheable('nbGames', function($games, $users, $user) {
            return $games->createByUserQuery($user)
                ->field('status')->greaterThanOrEq(Game::MATE)
                ->count();
        });
    }

    public function getNbWins()
    {
        return $this->cacheable('nbWins', function($games, $users, $user) {
            return $games->createByUserQuery($user)
                ->field('winnerUserId')->equals((string) $user->getId())
                ->count();
        });
    }

    public function getNbLosses()
    {
        return $this->cacheable('nbLosses', function($games, $users, $user) {
            return $games->createByUserQuery($user)
                ->field('winnerUserId')->exists(true)
                ->field('winnerUserId')->notEqual((string) $user->getId())
                ->count();
        });
    }

    public function getNbDraws()
    {
        return $this->getNbGames() - $this->getNbWins() - $this->getNbLosses();
    }

    public function getPercentWins()
    {
        if($this->getNbGames() > 0) {
            return round(100 * ($this->getNbWins() / $this->getNbGames()));
        }

        return 0;
    }

    protected function cacheable($cacheKey, \Closure $closure)
    {
        if(array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        return $this->cache[$cacheKey] = $closure($this->gameRepository, $this->userRepository, $this->user);
    }
}
