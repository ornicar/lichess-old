<?php

namespace Application\MessageBundle;

use Ornicar\MessageBundle\Model\MessageInterface;
use Ornicar\MessageBundle\Model\ParticipantInterface;

class Cache
{
    public function updateUnreadCache(ParticipantInterface $participant, $modifier = 0)
    {
        apc_store('nbm.'.$participant->getUsername(), $this->messageRepository->countUnreadByUser($participant) + $modifier);
    }

    public function getUnreadCacheForUsername($username)
    {
        return apc_fetch('nbm.'.$username) ?: 0;
    }

    public function countUnreadByParticipant(ParticipantInterface $participant)
    {
        return $this->getUnreadCacheForUsername($participant->getUsername());
    }
}
