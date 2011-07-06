<?php

namespace Application\MessageBundle\Security;

use Application\MessageBundle\Messenger;
use FOS\UserBundle\Model\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class InteractiveLoginListener
{
    protected $messenger;

    public function __construct(Messenger $messenger)
    {
        $this->messenger = $messenger;
    }

    public function listenToInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($user = $event->getAuthenticationToken()->getUser()) {
            if ($user instanceof User) {
                $this->messenger->updateUnreadCache($user);
            }
        }
    }
}
