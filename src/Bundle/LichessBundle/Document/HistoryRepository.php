<?php

namespace Bundle\LichessBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Application\UserBundle\Document\User;

class HistoryRepository extends DocumentRepository
{
    public function findOneByUser(User $user)
    {
        return $this->findOneBy(array('id' => $user->getUsernameCanonical()));
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
