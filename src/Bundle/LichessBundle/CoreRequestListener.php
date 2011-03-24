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

                    $chosenLanguage = $event->getRequest()->getPreferredLanguage(array_keys(
                        $this->container->getParameter('lichess.locales')
                    ));
                    $session->setLocale($chosenLanguage);
                    $session->setFlash('locale_change', $chosenLanguage);

                    $preferredLanguage = $this->getPreferredLanguage($event->getRequest());
                    $session->set('locale_name', $this->container->get('lichess_translation.manager')->getLanguageName($preferredLanguage));
                    if ($preferredLanguage && $chosenLanguage != $preferredLanguage) {
                        $session->setFlash('locale_missing', $preferredLanguage);
                    }
                }
            }
        }
    }

    private function getPreferredLanguage(Request $request)
    {
        foreach ($request->getLanguages() as $language) {
            if (preg_match('/^[a-z]{2,3}$/i', $language)) {
                return $language;
            }
        }
    }
}
