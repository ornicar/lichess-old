<?php

namespace Application\UserBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MongoId;
use MongoRegex;

class UserRepository extends DocumentRepository
{
    public function findOneByUsernameCanonical($username)
    {
        return $this->findOneBy(array('usernameCanonical' => $username));
    }

    public function findByUsernameCanonicals(array $usernames)
    {
        return $this->createQueryBuilder()
            ->field('usernameCanonical')->in($usernames)
            ->getQuery()->execute();
    }

    public function setOnline(User $user)
    {
        $query = array('_id' => new MongoId($user->getId()));
        $update = array('$set' => array('isOnline' => true));
        $this->dm->getDocumentCollection($this->documentName)->getMongoCollection()->update($query, $update);
    }

    /**
     * Gets the elo of each user
     *
     * @return array of int Elo
     */
    public function getElosArray()
    {
        $result = $this->createQueryBuilder()
            ->select('elo')
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        return array_values(array_map(function(array $data) { return (int)$data['elo']; }, $result));
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

    public function findUsernamesBeginningWith($term)
    {
        $query = array('usernameCanonical' => new MongoRegex(sprintf('/^%s/', strtolower($term))));
        $users = $this->dm->getDocumentCollection($this->documentName)->getMongoCollection()->find($query, array('username' => true));

        return array_values(array_map(function(array $user) { return $user['username']; }, iterator_to_array($users)));
    }

    public function getUsernamesIndexedById()
    {
        $users = $this->dm->getDocumentCollection($this->documentName)->getMongoCollection()->find(array(), array('username' => true));

        return array_map(function(array $user) { return $user['username']; }, iterator_to_array($users));
    }

    /**
     * Get the rank of the user starting from 1 (better)
     *
     * @return int
     **/
    public function getUserRank(User $user)
    {
        return $this->createEnabledQueryBuilder()
            ->field('elo')->gt($user->getElo())
            ->hint(array('elo' => -1))
            ->getQuery()
            ->count()
            + 1;
    }

    /**
     * Return the number of users
     *
     * @return int
     **/
    public function getNbUsers()
    {
        return $this->createEnabledQueryBuilder()->getQuery()->count();
    }

    public function createEnabledQueryBuilder()
    {
      return $this->createQueryBuilder()
        ->field('enabled')->equals(true);
    }

    public function createEnabledSortedByEloQuery()
    {
        return $this->createEnabledQueryBuilder()
            ->sort('elo', 'desc');
    }
}
