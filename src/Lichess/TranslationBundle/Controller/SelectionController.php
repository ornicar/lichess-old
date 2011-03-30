<?php

namespace Lichess\TranslationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SelectionController extends Controller
{
    public function switchAction($locale)
    {
        if($this->get('lichess_translation.manager')->isAvailable($locale)) {
            $this->get('lichess_translation.switcher')->switchLocale($this->get('request')->getSession(), $locale);
        }
        $baseUrl   = $this->generateUrl('lichess_homepage', array(), true);
        $localeUrl = $this->generateUrl('lichess_translation_selection_switch', array('locale' => $locale), true);
        $referer   = $this->container->get('request')->server->get('HTTP_REFERER');
        if(empty($referer) || 0 != strpos($referer, $baseUrl) || 0 === strpos($referer, $localeUrl)) {
            $referer = $baseUrl;
        }

        return new RedirectResponse($referer);
    }

    public function listAction()
    {
        $locales = $this->container->get('lichess_translation.manager')->getAvailableLanguages();
        unset($locales[$this->container->get('session')->getLocale()]);
        ksort($locales);

        return $this->render('LichessTranslation:Selection:links.html.twig', array('locales' => $locales));
    }
}
