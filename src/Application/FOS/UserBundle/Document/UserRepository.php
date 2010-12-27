<?php

namespace Application\FOS\UserBundle\Document;

use Bundle\FOS\UserBundle\Document\UserRepository as BaseUserRepository;
use MongoId;

class UserRepository extends BaseUserRepository
{
    public function setOnline(User $user)
    {
        $query = array('_id' => new MongoId($user->getId()));
        $update = array('$set' => array('isOnline' => true));
        $this->dm->getDocumentCollection($this->documentName)->getMongoCollection()->update($query, $update);
    }

    public function findOnlineUsersSortByElo()
    {
        return $this->createQueryBuilder()
            ->field('isOnline')->equals(true)
            ->sort('elo', 'desc')
            ->getQuery()
            ->execute();
    }

    public function findOnlineUsers()
    {
        return $this->createQueryBuilder()
            ->field('isOnline')->equals(true)
            ->getQuery()
            ->execute();
    }

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
}
