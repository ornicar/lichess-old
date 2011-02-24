<?php

namespace Application\MessageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\EventDispatcher\Event;
use FOS\UserBundle\Model\User;

class LichessMessageBundle extends Bundle
{
    public function boot()
    {
        $this->container->get('event_dispatcher')->connect('security.interactive_login', array($this, 'listenToLogin'));
    }

    public function listenToLogin(Event $event)
    {
        if ($user = $event->get('token')->getUser()) {
            if ($user instanceof User) {
                $this->container->get('ornicar_message.messenger')->updateUnreadCache($user);
            }
        }
    }

    public function getParent()
    {
        return 'OrnicarMessageBundle';
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return __DIR__;
    }
}
