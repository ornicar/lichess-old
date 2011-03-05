<?php

namespace Bundle\LichessBundle;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;
use DateTime;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Bundle\LichessBundle\Util\KeyGenerator;

class CoreRequestListener
{
    protected $languageCodes;

    public function __construct(array $locales)
    {
        $this->languageCodes = array_keys($locales);
    }

    public function listenToCoreRequest(Event $event)
    {
        if(HttpKernelInterface::MASTER_REQUEST === $event->get('request_type')) {
            $session = $event->get('request')->getSession();
            if(!$session->has('lichess.sound.enabled')) {
                $session->set('lichess.sound.enabled', true);
            }
            if(!$session->has('lichess.session_id')) {
                $session->set('lichess.session_id', KeyGenerator::generate(10));
                $bestLocale = $event->get('request')->getPreferredLanguage($this->languageCodes);
                $session->setLocale($bestLocale);
                $session->setFlash('locale_change', $bestLocale);
            }
        }
    }
}
