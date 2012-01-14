<?php

namespace Lichess\OpeningBundle\Document;

class MessageRepository extends TimelineRepository
{
    public function add(Message $message)
    {
        $this->dm->persist($message);
    }

    public function findByUsername($username, $nb = 50)
    {
      return $this->createQueryBuilder()
        ->field('username')->equals($username)
        ->sort(array('_id' => 'desc'))
        ->limit($nb)
        ->getQuery()
        ->execute();
    }

    public function findLastByUsername($username)
    {
      return $this->createQueryBuilder()
        ->field('username')->equals($username)
        ->sort(array('_id' => 'desc'))
        ->getQuery()
        ->getSingleResult();
    }
}
