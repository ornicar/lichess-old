<?php

namespace Bundle\LichessBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Bundle\LichessBundle\Document\Game;

class RoomRepository extends DocumentRepository
{
    public function findOneByGame(Game $game)
    {
        return $this->findOneBy(array('id' => $game->getId()));
    }

    protected $roomCache = array();
    public function findOneByGameOrCreate(Game $game)
    {
        if (isset($this->roomCache[$game->getId()])) {
            $room = $this->roomCache[$game->getId()];
        } elseif (!$room = $this->findOneByGame($game)) {
            $room = new Room($game->getId());
            $this->dm->persist($room);
        }
        $this->roomCache[$game->getId()] = $room;

        return $room;
    }

    public function findOneByUserOrCreate(User $user)
    {
        $history = $this->findOneByUser($user);

        if (null === $history) {
            $class = $this->getDocumentName();
            $history = new $class($user);
            $this->dm->persist($history);
            $this->dm->flush();
        }

        return $history;
    }
}
