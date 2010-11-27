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
            ->field('elo')->GreaterThan($user->getElo())
            ->getQuery()
            ->count()
            + 1;
    }
}
