<?php

namespace Application\UserBundle;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Application\UserBundle\Util\KeyGenerator;
use Application\UserBundle\Document\User;
use Application\UserBundle\Online\Cache as OnlineCache;
use DateTime;

class KernelRequestListener
{
    protected $securityContext;
    protected $onlineCache;

    public function __construct(SecurityContext $securityContext, OnlineCache $onlineCache)
    {
        $this->securityContext = $securityContext;
        $this->onlineCache     = $onlineCache;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if(HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            if(!$event->getRequest()->isXmlHttpRequest()) {
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
