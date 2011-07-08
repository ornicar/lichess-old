<?php

namespace Bundle\LichessBundle;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Lichess\TranslationBundle\Switcher;

class KernelRequestListener
{
    protected $translationSwitcher;

    public function __construct(Switcher $translationSwitcher)
    {
        $this->translationSwitcher = $translationSwitcher;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if(HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            if ($response = $this->translationSwitcher->switchLocaleForRequest($event->getRequest())) {
                $event->setResponse($response);
            }
        }
    }
}
