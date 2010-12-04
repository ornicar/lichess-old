<?php

namespace Bundle\LichessBundle\Entity;
use Doctrine\ORM\EntityRepository;

class TranslationRepository extends EntityRepository
{
    public function findAllSortByCreatedAt()
    {
        return $this->createQueryBuilder()
            ->sort('createdAt', 'DESC')
            ->getQuery()->execute();
    }
}
