<?php

namespace Bundle\LichessBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use FOS\UserBundle\Model\User;

class TrialRepository extends DocumentRepository
{
    /**
     * Return the number of unresolved games
     *
     * @return int
     **/
    public function getNbUnresolved()
    {
        return $this->createUnresolvedQuery()->getQuery()->count();
    }

    /**
     * Return the number of guilty games
     *
     * @return int
     **/
    public function getNbGuilty()
    {
        return $this->createGuiltyQuery()->getQuery()->count();
    }

    /**
     * Return the number of innocent games
     *
     * @return int
     **/
    public function getNbInnocent()
    {
        return $this->createInnocentQuery()->getQuery()->count();
    }

    public function createUnresolvedQuery()
    {
        return $this->createVerdictQuery(null);
    }

    public function createGuiltyQuery()
    {
        return $this->createVerdictQuery(true);
    }

    public function createInnocentQuery()
    {
        return $this->createVerdictQuery(false);
    }

    protected function createVerdictQuery($verdict)
    {
        return $this->createQueryBuilder()
            ->field('verdict')->equals($verdict)
            ->sort('score', 'desc');
    }
}
