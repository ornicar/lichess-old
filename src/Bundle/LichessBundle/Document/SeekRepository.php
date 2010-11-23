<?php

namespace Bundle\LichessBundle\Document;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Bundle\LichessBundle\Document\Game;

class SeekRepository extends DocumentRepository
{
    public function findAllSortByCreatedAt()
    {
        return $this->createQuery()
            ->sort('createdAt', 'ASC')
            ->execute();
    }

    public function findOneByGame(Game $game)
    {
        return $this->createQuery()
            ->field('game.$id')->equals($game->getId())
            ->getSingleResult();
    }
}
