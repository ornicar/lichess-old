<?php

namespace Application\MessageBundle\Security;

use Application\MessageBundle\Messenger;
use FOS\UserBundle\Model\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;

class InteractiveLoginListener
{
    protected $messenger;

    public function __construct(Messenger $messenger)
    {
        $this->messenger = $messenger;
    }

    public function listenToInteractiveLogin(Event $event)
    {
        if ($user = $event->get('token')->getUser()) {
            if ($user instanceof User) {
                $this->messenger->updateUnreadCache($user);
            }
        }
    }
}
