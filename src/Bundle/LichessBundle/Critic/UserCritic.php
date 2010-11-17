<?php

namespace Bundle\LichessBundle\Critic;
use Bundle\DoctrineUserBundle\Document\User;
use Bundle\LichessBundle\Document\GameRepository;
use Bundle\LichessBundle\Document\Game;

class UserCritic
{
    protected $user;
    protected $gameRepository;
    protected $cache;

    public function __construct(GameRepository $gameRepository)
    {
        $this->gameRepository = $gameRepository;
    }

    public function setUser(User $user)
    {
        $this->cache = array();
        $this->user = $user;
    }

    public function cacheable($cacheKey, \Closure $closure)
    {
        if(array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        return $this->cache[$cacheKey] = $closure($this->gameRepository, $this->user);
    }

    public function getNbGames()
    {
        return $this->cacheable('nbGames', function($games, $user) {
            return $games->createByUserQuery($user)
                ->field('status')->greaterThanOrEq(Game::MATE)
                ->count();
        });
    }

    public function getNbWins()
    {
        return $this->cacheable('nbWins', function($games, $user) {
            return $games->createByUserQuery($user)
                ->field('winnerUserId')->equals((string) $user->getId())
                ->count();
        });
    }

    public function getNbDefeats()
    {
        return $this->cacheable('nbDefeats', function($games, $user) {
            return $games->createByUserQuery($user)
                ->field('winnerUserId')->exists(true)
                ->field('winnerUserId')->notEqual((string) $user->getId())
                ->count();
        });
    }

    public function getNbDraws()
    {
        return $this->cacheable('nbDraws', function($games, $user) {
            $games->createByUserQuery($user)
                ->field('status')->equals(Game::DRAW)
                ->count();
        });
    }

    public function getPercentWins()
    {
        if($this->getNbGames() > 0) {
            return round(100 * ($this->getNbWins() / $this->getNbGames()));
        }

        return 0;
    }
}
