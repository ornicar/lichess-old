<?php

namespace Application\DoctrineUserBundle\Document;

use Bundle\DoctrineUserBundle\Document\UserRepository as BaseUserRepository;

class UserRepository extends BaseUserRepository
{
    /**
     * Get the rank of the user starting from 1 (better)
     *
     * @return int
     **/
    public function getUserRank(User $user)
    {
        return $this->createQueryBuilder()
            ->field('elo')->gt($user->getElo())
            ->getQuery()
            ->count()
            + 1;
    }

    /**
     * @return Doctrine\ODM\MongoDB\QueryBuilder
     **/
    public function createRecentQuery()
    {
        return $this->createQueryBuilder()
            ->sort('elo', 'desc');
    }

    /**
     * @return int
     **/
    public function getCount()
    {
        return $this->createQueryBuilder()
            ->getQuery()
            ->count();
    }
}
