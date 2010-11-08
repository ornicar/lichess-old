<?php

namespace Bundle\LichessBundle;
use Bundle\LichessBundle\Util\KeyGenerator;

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
                $session = $container->getSessionService();
                if(!$session->has('lichess.sound.enabled')) {
                    $session->set('lichess.sound.enabled', true);
                }
                if(!$session->has('lichess.session_id')) {
                    $session->set('lichess.session_id', KeyGenerator::generate(10));
                    $languages = $container->getParameter('lichess.locales');
                    $bestLocale = $container->get('request')->getPreferredLanguage($languages);
                    $session->setLocale($languages);
                }
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
