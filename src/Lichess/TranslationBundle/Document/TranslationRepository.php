<?php

namespace Lichess\TranslationBundle\Document;
use Doctrine\ODM\MongoDB\DocumentRepository;

class TranslationRepository extends DocumentRepository
{
    public function findAllSortByCreatedAt()
    {
        return $this->createQueryBuilder()
            ->sort('_id', 'DESC')
            ->getQuery()->execute();
    }
}
