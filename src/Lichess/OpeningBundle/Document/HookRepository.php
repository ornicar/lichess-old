<?php

namespace Lichess\OpeningBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class HookRepository extends DocumentRepository
{
    public function findOneByOwnerId($id)
    {
        return $this->createQueryBuilder()
            ->field('ownerId')->equals($id)
            ->getQuery()
            ->getSingleResult();
    }

    public function findOneOpenById($id)
    {
        return $this->createQueryBuilder()
            ->field('id')->equals($id)
            ->field('match')->equals(false)
            ->getQuery()
            ->getSingleResult();
    }

    public function findAllOpen()
    {
        return $this->createQueryBuilder()
            ->field('match')->equals(false)
            ->sort('createdAt', 'asc')
            ->getQuery()
            ->execute();
    }

    public function findAllOpenCasual()
    {
        return $this->createQueryBuilder()
            ->field('match')->equals(false)
            ->field('mode')->equals(0)
            ->sort('createdAt', 'asc')
            ->getQuery()
            ->execute();
    }
}
