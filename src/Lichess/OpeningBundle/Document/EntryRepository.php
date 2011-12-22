<?php

namespace Lichess\OpeningBundle\Document;

class EntryRepository extends TimelineRepository
{
    public function add(Entry $entry)
    {
        $this->dm->persist($entry);
    }
}
