<?php

namespace Bundle\LichessBundle\Entity;

class TranslationRepository extends ObjectRepository
{
    public function findAllSortByCreatedAt()
    {
        return $this->createQueryBuilder()
            ->orderBy('createdAt', 'DESC')
            ->getQuery()->execute();
    }
}
