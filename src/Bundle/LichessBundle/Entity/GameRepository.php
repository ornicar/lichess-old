<?php

namespace Bundle\LichessBundle\Entity;
use Bundle\DoctrineUserBundle\Model\User;
use Bundle\LichessBundle\Model;

class GameRepository extends ObjectRepository implements Model\GameRepository
{
    /**
     * Find all games played by a user
     *
     * @return array
     **/
    public function findRecentByUser(User $user)
    {
        return $this->createRecentByUserQuery($user)
            ->getQuery()
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
        return 1 === $this->createQueryBuilder('g')
            ->where('g.id = ?1')
            ->setParameter(1, $id)
            ->select('COUNT(g.id)')
            ->getQuery()->getSingleScalarResult();
    }

    /**
     * Find ids of more recent games
     *
     * @return array
     **/
    public function findRecentStartedGameIds($nb)
    {
        $data = $this->createRecentQuery()
            ->where('g.status = ?1')
            ->setParameter(1, Game::STARTED)
            ->select('g.id')
            ->setMaxResults($nb)
            ->getQuery()->getArrayResult();

        $data = array_map(function($item) {
            return $item['id'];
        }, $data);
        
        return $data;
    }

    /**
     * Find games for the given ids, in the ids order
     *
     * @return array
     **/
    public function findGamesByIds($ids)
    {
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        $qb = $this->createQueryBuilder('g');

        $games = $qb->add('where', $qb->expr()->in('g.id', (array) $ids))
            ->getQuery()
            ->getResult();

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
        return $this->createQueryBuilder('g')
                ->select('COUNT(g.id)')
                ->getQuery()
                ->getSingleScalarResult();
    }

    /**
     * Return the number of mates
     *
     * @return int
     **/
    public function getNbMates()
    {
        return $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.status = ?1')
            ->setParameter(1, Game::MATE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Return the number of wins
     *
     * @return int
     **/
    public function getNbWins(User $user)
    {
        return $this->createByUserQuery($user)
            ->select('COUNT(g.id)')
            ->andWhere('g.winnerUserId = ?2')
            ->setParameter(2, $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Return the number of losses
     *
     * @return int
     **/
    public function getNbLosses(User $user)
    {
        return $this->createByUserQuery($user)
            ->select('COUNT(g.id)')
            ->andWhere('g.winnerUserId != ?2')
            ->setParameter(2, $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Return the number of user games
     *
     * @return int
     **/
    public function getNbUserGames(User $user)
    {
        return $this->createByUserQuery($user)
            ->select('COUNT(g.id)')
            ->andWhere('g.status > ?2')
            ->setParameter(2, Game::MATE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Query of all games ordered by updatedAt
     *
     * @return Doctrine\ORM\QueryBuilder
     **/
    public function createRecentQuery()
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.updatedAt', 'DESC');
    }

    /**
     * Query of games played by a user ordered by updatedAt
     *
     * @param  User $user
     * @return Doctrine\ORM\QueryBuilder
     **/
    public function createRecentByUserQuery(User $user)
    {
        return $this->createByUserQuery($user)
            ->orderBy('g.updatedAt', 'DESC');
    }

    /**
     * Query of games played by a user
     *
     * @param  User $user
     * @return Doctrine\ORM\QueryBuilder
     **/
    public function createByUserQuery(User $user)
    {
        return $this->createQueryBuilder('g')
            ->where('g.userIds = ?1')
            ->setParameter(1, (string) $user->getId());
    }

    /**
     * Query of at least started games of a user
     *
     * @return Doctrine\ORM\QueryBuilder
     **/
    public function createRecentStartedOrFinishedByUserQuery(User $user)
    {
        return $this->createRecentByUserQuery($user)
            ->andWhere('g.status > ?2')
            ->setParameter(2, Game::STARTED)
            ->getQuery();
    }

    /**
     * Query of at least started games
     *
     * @return Doctrine\ORM\Query
     **/
    public function createRecentStartedOrFinishedQuery()
    {
        return $this->createRecentQuery()
            ->where('g.status > ?1')
            ->setParameter(1, Game::STARTED)
            ->getQuery();
    }

    /**
     * Query of at least mate games
     *
     * @return Doctrine\ORM\Query
     **/
    public function createRecentMateQuery()
    {
        return $this->createRecentQuery()
            ->where('g.status = ?1')
            ->setParameter(1, Game::MATE)
            ->getQuery();
    }

    public function findSimilar(Model\Game $game, \DateTime $since)
    {
        $qb = $this->createQueryBuilder('g');

        return $qb->add('where', $qb->expr()->in('g.pgnMoves', (array) $game->getPgnMoves()), true)
            ->where('g.id != ?1')
            ->andWhere('g.updatedAt > ?2')
            ->andWhere('g.status = ?3')
            ->andWhere('g.turns = ?4')
            ->setParameter(1, $game->getId())
            ->setParameter(2, $since->getTimestamp())
            ->setParameter(3, Game::STARTED)
            ->setParameter(4, $game->getTurns())
            //->hint(array('updatedAt' => -1)) @todo what is this?
            ->getQuery()
            ->execute();
    }
}
