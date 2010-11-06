<?php

namespace Bundle\LichessBundle\Document;
use Doctrine\ODM\MongoDB\DocumentRepository;

class SeekRepository extends DocumentRepository
{
    public function findAllSortByCreatedAt()
    {
        return $this->createQuery()
            ->sort('createdAt', 'ASC')
            ->execute();
    }
}
