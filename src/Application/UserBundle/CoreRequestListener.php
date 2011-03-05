<?php

namespace Application\UserBundle;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\SecurityContext;
use DateTime;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Application\UserBundle\Util\KeyGenerator;
use Application\UserBundle\Document\User;
use Application\UserBundle\Online\Cache as OnlineCache;

class CoreRequestListener
{
    protected $securityContext;
    protected $onlineCache;

    public function __construct(SecurityContext $securityContext, OnlineCache $onlineCache)
    {
        $this->securityContext = $securityContext;
        $this->onlineCache     = $onlineCache;
    }

    public function listenToCoreRequest(Event $event)
    {
        if(HttpKernelInterface::MASTER_REQUEST === $event->get('request_type')) {
            if(!$event->get('request')->isXmlHttpRequest()) {
                if($token = $this->securityContext->getToken()) {
                    if($user = $token->getUser()) {
                        if($user instanceof User) {
                            $this->onlineCache->setOnline($user);
                        }
                    }
                }
            }
        }
    }
}
