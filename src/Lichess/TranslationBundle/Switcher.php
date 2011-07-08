<?php

namespace Lichess\TranslationBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Changes the user language
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Switcher
{
    protected $manager;
    protected $translator;

    /**
     * Instanciates a new language switcher
     **/
    public function __construct(TranslationManager $manager, TranslatorInterface $translator)
    {
        $this->manager = $manager;
        $this->translator = $translator;
    }

    /**
     * Chooses a language for a request
     *
     * @return $response or null
     **/
    public function switchLocaleForRequest(Request $request)
    {
        $host = $request->getHost();
        if ($dotPos = strrpos($host, '.')) {
            $locale = substr($host, 0, $dotPos);
            if ($this->manager->isAvailable($locale)) {
                $request->server->set('LICHESS_LOCALE', $locale);
                $request->server->set('LICHESS_LOCALE_NAME', $this->manager->getAvailableLanguageName($locale));
                $this->translator->setLocale($locale);
                return;
            }
            // remove bad locale from the host name
            $host = substr($host, $dotPos+1);
        }
        $locale = $request->getPreferredLanguage($this->manager->getAvailableLanguageCodes());
        $url = sprintf('http://%s.%s%s', $locale, $host, $request->getRequestUri());
        $response = new RedirectResponse($url);

        $preferredLanguage = $this->getRequestPreferredLanguage($request);
        if ($preferredLanguage && $locale != $preferredLanguage) {
            $allLanguageCodes = array_keys($this->manager->getLanguages());
            if (in_array($preferredLanguage, $allLanguageCodes)) {
                $request->getSession()->setFlash('locale_missing', $preferredLanguage);
            }
        }

        return $response;
    }

    private function getRequestPreferredLanguage(Request $request)
    {
        foreach ($request->getLanguages() as $language) {
            if (preg_match('/^[a-z]{2,3}$/i', $language)) {
                return $language;
            }
        }
    }
}
