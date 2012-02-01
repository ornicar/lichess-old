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

    public function findSince($id)
    {
        return $this->createQueryBuilder()
            ->field('_id')->gt((int) $id)
            ->sort('_id', 'desc')
            ->hydrate(false)
            ->getQuery()
            ->execute();
    }

    public function getLastId()
    {
        $res = array_values(iterator_to_array($this->createQueryBuilder()
            ->select('_id')
            ->sort('_id', 'desc')
            ->limit(1)
            ->hydrate(false)
            ->getQuery()
            ->execute()));

        return empty($res) ? 0 : $res[0]['_id'];
    }
}
