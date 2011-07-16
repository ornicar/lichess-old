<?php

namespace Lichess\MessageBundle\Security;

use Lichess\MessageBundle\Cache;
use FOS\UserBundle\Model\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class InteractiveLoginListener
{
    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function listenToInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($user = $event->getAuthenticationToken()->getUser()) {
            if ($user instanceof User) {
                $this->cache->updateUnreadCache($user);
            }
        }
    }
}
