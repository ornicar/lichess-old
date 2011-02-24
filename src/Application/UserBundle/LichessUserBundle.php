<?php

namespace Application\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use FOS\UserBundle\Model\User;

class LichessUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
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

    public function boot()
    {
        $this->container->get('event_dispatcher')->connect('core.response', array($this, 'listenToCoreResponseEvent'));
    }

    public function listenToCoreResponseEvent(Event $event, $response)
    {
        if(HttpKernelInterface::MASTER_REQUEST === $event->get('request_type')) {
            if(!$this->container->get('request')->isXmlHttpRequest()) {
                if($token = $this->container->get('security.context')->getToken()) {
                    if($user = $token->getUser()) {
                        if($user instanceof User) {
                            $this->container->get('lichess_user.online.cache')->setOnline($user);
                        }
                    }
                }
            }
        }

        return $response;
    }
}
