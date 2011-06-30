<?php

namespace Lichess\TimelineBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use FOS\UserBundle\Model\User;
use DateTime;
use MongoDate;

class TimelineEntryRepository extends DocumentRepository
{
    public function add($type, $html, User $user = null)
    {
        $entry = new TimelineEntry();
        $entry->setType($type);
        $entry->setHtml($html);
        $entry->setAuthor($user);

        $this->dm->persist($entry);
    }

    public function findLatests($nb)
    {
        return $this->createQueryBuilder()
            ->sort('createdAt', 'desc')
            ->limit($nb)
            ->getQuery()
            ->execute();
    }
}
