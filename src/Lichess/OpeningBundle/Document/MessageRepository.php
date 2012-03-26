<?php

namespace Lichess\OpeningBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class MessageRepository extends DocumentRepository
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
