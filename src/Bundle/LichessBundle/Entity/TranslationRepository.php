<?php

namespace Bundle\LichessBundle\Entity;

class TranslationRepository extends ObjectRepository
{
    public function findAllSortByCreatedAt()
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()->execute();
    }
}
