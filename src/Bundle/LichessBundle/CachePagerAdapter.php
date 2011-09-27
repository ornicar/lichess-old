<?php

namespace Bundle\LichessBundle;

use Doctrine\ODM\MongoDB\Query\Builder;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;

/**
 * This adapter lets you set the nbResult
 */
class CachePagerAdapter extends DoctrineODMMongoDBAdapter
{
    private $nbResults;

    /**
     * Sets the nbResults
     *
     * @return null
     */
    public function setNbResults($nbResults)
    {
        $this->nbResults = $nbResults;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        if ($this->nbResults) {
            return $this->nbResults;
        }

        return $this->queryBuilder->getQuery()->count();
    }
}
