<?php

namespace Lichess\OpeningBundle\Document;

class MessageRepository extends TimelineRepository
{
    public function add(Message $message)
    {
        $this->dm->persist($message);
    }
}
