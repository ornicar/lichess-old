<?php

namespace Application\FOS\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Bundle\FOS\UserBundle\Model\User;

class FOSUserBundle extends BaseBundle
{

    public function boot()
    {
        $this->container->get('event_dispatcher')->connect('core.response', array($this, 'listenToCoreResponseEvent'));
    }

    public function listenToCoreResponseEvent(Event $event, $response)
    {
        if(HttpKernelInterface::MASTER_REQUEST === $event->get('request_type')) {
            if(!$this->container->get('request')->isXmlHttpRequest()) {
                if($user = $this->container->get('security.context')->getUser()) {
                    if($user instanceof User) {
                        $this->container->get('fos_user.onliner')->setOnline($user);
                    }
                }
            }
        }

        return $response;
    }
}
