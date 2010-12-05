<?php

namespace Bundle\LichessBundle\Document;
use Bundle\LichessBundle\Document\Game;

class SeekRepository extends ObjectRepository
{
    public function findAllSortByCreatedAt()
    {
        return $this->createQueryBuilder()
            ->sort('createdAt', 'ASC')
            ->getQuery()->execute();
    }

    public function findOneByGame(Game $game)
    {
        return $this->createQueryBuilder()
            ->field('game.$id')->equals($game->getId())
            ->getQuery()->getSingleResult();
    }
}
