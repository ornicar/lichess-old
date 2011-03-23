<?php

namespace Bundle\LichessBundle\Document;
use Doctrine\ODM\MongoDB\DocumentRepository;

class TranslationRepository extends DocumentRepository
{
    public function findAllSortByCreatedAt()
    {
        return $this->createQueryBuilder()
            ->sort('createdAt', 'DESC')
            ->getQuery()->execute();
    }
}
