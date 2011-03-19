<?php

namespace Bundle\LichessBundle;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Bundle\LichessBundle\Util\KeyGenerator;
use DateTime;

class CoreRequestListener
{
    protected $languageCodes;

    public function __construct(array $locales)
    {
        $this->languageCodes = array_keys($locales);
    }

    public function onCoreRequest(GetResponseEvent $event)
    {
        if(HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $session = $event->getRequest()->getSession();
            if(!$session->has('lichess.sound.enabled')) {
                $session->set('lichess.sound.enabled', true);
            }
            if(!$session->has('lichess.session_id') || true) {
                $session->set('lichess.session_id', KeyGenerator::generate(10));

                $preferredLanguages = $event->getRequest()->getLanguages();
                $preferredLanguage = $preferredLanguages[0];
                $chosenLanguage = $event->getRequest()->getPreferredLanguage($this->languageCodes);
                $session->setLocale($chosenLanguage);
                $session->setFlash('locale_change', $chosenLanguage);
                if ($chosenLanguage != $preferredLanguage) {
                    $session->setFlash('locale_missing', $preferredLanguage);
                }
            }
        }
    }
}
