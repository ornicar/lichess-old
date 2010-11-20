<?php

namespace Bundle\LichessBundle\Document;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Bundle\DoctrineUserBundle\Model\User;

class GameRepository extends DocumentRepository
{
    /**
     * Find all games played by a user
     *
     * @return array
     **/
    public function findRecentByUser(User $user)
    {
        return $this->createRecentByUserQuery($user)
            ->execute();
    }

    /**
     * Finds one game by its Id
     *
     * @param string $id
     * @return Game or null
     **/
    public function findOneById($id)
    {
        return $this->find($id);
    }

    /**
     * Tells if a game with this id exists
     *
     * @param string $id
     * @return bool
     */
    public function existsById($id)
    {
        return 1 === $this->createQuery()
            ->field('id')->equals($id)
            ->count();
    }

    /**
     * Find ids of more recent games
     *
     * @return array
     **/
    public function findRecentStartedGameIds($nb)
    {
        $data = $this->createRecentQuery()
            ->hydrate(false)
            ->field('status')->equals(Game::STARTED)
            ->select('id')
            ->limit($nb)
            ->execute();
        $ids = array_keys(iterator_to_array($data));

        return $ids;
    }

    /**
     * Find games for the given ids, in the ids order
     *
     * @return array
     **/
    public function findGamesByIds($ids)
    {
        if(is_string($ids)) {
            $ids = explode(',', $ids);
        }

        $games = $this->createQuery()
            ->field('_id')->in($ids)
            ->execute();

        $games = iterator_to_array($games);

        // sort games in the order of ids
        $idPos = array_flip($ids);
        usort($games, function($a, $b) use ($idPos)
        {
            return $idPos[$a->getId()] > $idPos[$b->getId()];
        });

        return $games;
    }

    /**
     * Return the number of games
     *
     * @return int
     **/
    public function getNbGames()
    {
        return $this->createQuery()->count();
    }

    /**
     * Return the number of mates
     *
     * @return int
     **/
    public function getNbMates()
    {
        return $this->createQuery()
            ->field('status')->equals(Game::MATE)
            ->count();
    }

    /**
     * Query of all games ordered by updatedAt
     *
     * @return Doctrine\ODM\Mongodb\Query
     **/
    public function createRecentQuery()
    {
        return $this->createQuery()
            ->sort('updatedAt', 'DESC');
    }

    /**
     * Query of games played by a user ordered by updatedAt
     *
     * @param  User $user
     * @return Doctrine\ODM\Mongodb\Query
     **/
    public function createRecentByUserQuery(User $user)
    {
        return $this->createByUserQuery($user)
            ->sort('updatedAt', 'DESC');
    }

    /**
     * Query of games played by a user
     *
     * @param  User $user
     * @return Doctrine\ODM\Mongodb\Query
     **/
    public function createByUserQuery(User $user)
    {
        return $this->createQuery()
            ->field('userIds')->equals((string) $user->getId());
    }

    /**
     * Query of at least started games of a user
     *
     * @return Doctrine\ODM\Mongodb\Query
     **/
    public function createRecentStartedOrFinishedByUserQuery(User $user)
    {
        return $this->createRecentByUserQuery($user)
            ->field('status')->greaterThanOrEq(Game::STARTED);
    }

    /**
     * Query of at least started games
     *
     * @return Doctrine\ODM\Mongodb\Query
     **/
    public function createRecentStartedOrFinishedQuery()
    {
        return $this->createRecentQuery()
            ->field('status')->greaterThanOrEq(Game::STARTED);
    }

    /**
     * Query of at least mate games
     *
     * @return Doctrine\ODM\Mongodb\Query
     **/
    public function createRecentMateQuery()
    {
        return $this->createRecentQuery()
            ->field('status')->equals(Game::MATE);
    }

    public function findSimilar(Game $game, \DateTime $since)
    {
        return $this->createQuery()
            ->field('id')->notEqual($game->getId())
            ->field('updatedAt')->greaterThan($since)
            ->field('status')->equals(Game::STARTED)
            ->field('turns')->equals($game->getTurns())
            ->field('pgnMoves')->equals($game->getPgnMoves())
            ->hint(array('updatedAt' => -1))
            ->execute();
    }
}
