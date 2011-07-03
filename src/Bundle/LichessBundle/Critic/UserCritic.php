<?php

namespace Bundle\LichessBundle\Critic;
use FOS\UserBundle\Document\User;
use Bundle\LichessBundle\Document\GameRepository;
use Bundle\LichessBundle\Document\Game;
use Application\UserBundle\Document\UserRepository;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Bundle\LichessBundle\Elo\Calculator;

class UserCritic
{
    protected $user;
    protected $gameRepository;
    protected $userRepository;
    protected $securityContext;
    protected $cache;

    public function __construct(GameRepository $gameRepository, UserRepository $userRepository, Calculator $calculator, SecurityContextInterface $securityContext)
    {
        $this->gameRepository = $gameRepository;
        $this->userRepository = $userRepository;
        $this->calculator = $calculator;
        $this->securityContext = $securityContext;
    }

    public function setUser(User $user)
    {
        $this->cache = array();
        $this->user = $user;
    }

    public function getEloIfWin()
    {
        return '+'.$this->calculator->calculateDiff($this->getAuthenticatedUser()->getElo(), $this->user->getElo(), -1);
    }

    public function getEloIfDraw()
    {
        $diff = $this->calculator->calculateDiff($this->getAuthenticatedUser()->getElo(), $this->user->getElo(), 0);
        if ($diff > 0) $diff = '+'.$diff;

        return $diff;
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
            return $users->createQueryBuilder()->getQuery()->count();
        });
    }

    public function getNbGames()
    {
        return $this->cacheable('nbGames', function($games, $users, $user) {
            return $games->createByUserQuery($user)
                ->field('status')->gte(Game::MATE)
                ->getQuery()->count();
        });
    }

    public function getNbGamesWithMe()
    {
        $key = 'nbGamesWithMe';
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $this->gameRepository->createByUsersQuery($this->user, $this->getAuthenticatedUser())->getQuery()->count();
        }

        return $this->cache[$key];
    }

    public function getNbWins()
    {
        return $this->cacheable('nbWins', function($games, $users, $user) {
            return $games->createByUserQuery($user)
                ->field('winnerUserId')->equals((string) $user->getId())
                ->getQuery()->count();
        });
    }

    public function getNbLosses()
    {
        return $this->cacheable('nbLosses', function($games, $users, $user) {
            return $games->createByUserQuery($user)
                ->field('winnerUserId')->exists(true)
                ->field('winnerUserId')->notEqual((string) $user->getId())
                ->getQuery()->count();
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

    protected function getAuthenticatedUser()
    {
        return $this->securityContext->getToken()->getUser();
    }
}
