<?php

namespace Bundle\LichessBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Bundle\LichessBundle\Util\KeyGenerator;
use Symfony\Component\HttpFoundation\Request;
use DateTime;

class KernelRequestListener
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if(HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            if(!$event->getRequest()->getSession()->has('lichess.sound.enabled')) {
                $event->getRequest()->getSession()->set('lichess.sound.enabled', true);
            }
            if ($response = $this->container->get('lichess_translation.switcher')->switchLocaleForRequest($event->getRequest())) {
                $event->setResponse($response);
            }
        }
    }
}
