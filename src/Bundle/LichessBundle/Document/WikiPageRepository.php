<?php

namespace Bundle\LichessBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class WikiPageRepository extends DocumentRepository
{
    public function replaceWith(array $wikiPages)
    {
        $this->createQueryBuilder()->remove()->getQuery()->execute();

        foreach ($wikiPages as $p) {
            $this->dm->persist($p);
        }
    }

    public function findAll()
    {
        return $this->createQueryBuilder()
            ->sort(array('name' => 'asc'))
            ->getQuery()
            ->execute();
    }
}
