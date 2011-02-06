<?php

namespace Application\MessageBundle;

use Bundle\Ornicar\MessageBundle\Model\Message;
use Bundle\Ornicar\MessageBundle\Messenger as BaseMessenger;
use FOS\UserBundle\Model\User;

class Messenger extends BaseMessenger
{
    public function send(Message $message)
    {
        parent::send($message);

        $this->updateUnreadCache($message->getTo(), +1);
    }

    public function markAsRead(Message $message)
    {
        parent::markAsRead($message);

        $this->updateUnreadCache($message->getTo(), -1);
    }

    protected function updateUnreadCache(User $user, $modifier = 0)
    {
        apc_store('nbm.'.$user->getUsername(), $this->messageRepository->countUnreadByUser($user) + $modifier);
    }

    public function getUnreadCacheForUsername($username)
    {
        return apc_fetch('nbm.'.$username);
    }
}
