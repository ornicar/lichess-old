<?php

namespace Bundle\LichessBundle\Document;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Model;

class SeekRepository extends ObjectRepository implements Model\SeekRepository
{
    public function findAllSortByCreatedAt()
    {
        return $this->createQueryBuilder()
            ->sort('createdAt', 'ASC')
            ->getQuery()->execute();
    }

    public function findOneByGame(Model\Game $game)
    {
        return $this->createQueryBuilder()
            ->field('game.$id')->equals($game->getId())
            ->getQuery()->getSingleResult();
    }
}
