<?php

namespace Application\DoctrineUserBundle\Entity;
use Bundle\DoctrineUserBundle\Entity\UserRepository as BaseUserRepository;

class UserRepository extends BaseUserRepository
{
    /**
     * Get the rank of the user starting from 1 (better)
     *
     * @return int
     **/
    public function getUserRank(User $user)
    {
        return $this->createQueryBuilder('g')
            ->where('g.elo > ?1')
            ->setParameter(1, $user->getElo())
            ->select('COUNT(g.id)')
            ->getQuery()
            ->getSingleScalarResult()
            + 1;
    }

    /**
     * @return Doctrine\ORM\QueryBuilder
     **/
    public function createRecentQuery()
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.elo', 'DESC')
            ->getQuery();
    }

    /**
     * @return int
     **/
    public function getCount()
    {
        return $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
