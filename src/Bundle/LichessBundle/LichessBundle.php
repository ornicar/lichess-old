<?php

namespace Bundle\LichessBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\Event;

class LichessBundle extends BaseBundle
{
    public function boot()
    {
        parent::boot();
        $container = $this->container;
        $container->getEventDispatcherService()->connect('core.request', function(Event $event) use ($container) {
            if(HttpKernelInterface::MASTER_REQUEST === $event->getParameter('request_type')) {
                $translator = $container->getLichessTranslatorService();
                $session = $container->getSessionService();
                if(!$session->has('lichess.sound.enabled')) {
                    $session->set('lichess.sound.enabled', true);
                }
                if(!$session->get('lichess.user_id')) {
                    $languages = $container->getRequestService()->getLanguages() ?: array();
                    $bestLocale = $translator->getBestLocale($languages);
                    $session->setLocale($bestLocale);
                    $session->set('lichess.user_id', session_id());
                }
                $translator->setLocale($session->getLocale());
            }
        });
    }

    /**
     * Get a DocumentRepository
     *
     * @param DocumentManager $objectManager a DocumentManager
     * @param string $objectClass the class of the document
     * @return DocumentRepository a DocumentRepository
     */
    public static function getRepository($objectManager, $objectClass)
    {
        return $objectManager->getRepository($objectClass);
    }
}
