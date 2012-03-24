<?php

namespace Lichess\OpeningBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

abstract class TimelineRepository extends DocumentRepository
{
    public function findRecent($nb, $hydrate = false)
    {
        return $this->createQueryBuilder()
            ->sort('_id', 'desc')
            ->limit($nb)
            ->hydrate($hydrate)
            ->getQuery()
            ->execute();
    }
}
