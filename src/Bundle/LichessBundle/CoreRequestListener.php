<?php

namespace Bundle\LichessBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Bundle\LichessBundle\Util\KeyGenerator;
use Symfony\Component\HttpFoundation\Request;
use DateTime;

class CoreRequestListener
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onCoreRequest(GetResponseEvent $event)
    {
        if(HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            if ($session = $event->getRequest()->getSession()) {
                if(!$session->has('lichess.sound.enabled')) {
                    $session->set('lichess.sound.enabled', true);
                }
                if(!$session->has('lichess.session_id')) {
                    $session->set('lichess.session_id', KeyGenerator::generate(10));
                    $this->container->get('lichess_translation.switcher')->switchLocaleForRequest($event->getRequest());
                }
            }
        }
    }
}
