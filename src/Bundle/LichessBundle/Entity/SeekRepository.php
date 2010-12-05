<?php

namespace Bundle\LichessBundle\Entity;
use Bundle\LichessBundle\Entity\Game;
use Bundle\LichessBundle\Model;

class SeekRepository extends ObjectRepository implements Model\SeekRepository
{
    public function findAllSortByCreatedAt()
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'ASC')
            ->getQuery()->execute();
    }

    public function findOneByGame(Model\Game $game)
    {
        return $this->createQueryBuilder('s')
            ->where('s.game = ?1')
            ->setParameter(1, $game->getId())
            ->getQuery()->getSingleResult();
    }
}
