<?php

namespace Lichess\TranslationBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Changes the user language
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Switcher
{
    protected $manager;

    /**
     * Instanciates a new language switcher
     **/
    public function __construct(TranslationManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Chooses a language for a request
     *
     * @return $response or null
     **/
    public function switchLocaleForRequest(Request $request)
    {
        $session = $request->getSession();
        $parts = explode('.', $request->getHost());
        $locale = $parts[0];
        $localeLen = strlen($locale);
        if ($localeLen === 2 || $localeLen === 3) {
            if ($locale === $session->getLocale()) {
                return;
            }
            if ($this->manager->isAvailable($locale)) {
                $session->setLocale($locale);
                $preferred = $request->getPreferredLanguage($this->manager->getAvailableLanguageCodes());
                if ($preferred != $locale) {
                    $session->setFlash('locale_change_adjust', $preferred);
                } else {
                    $session->setFlash('locale_change_contribute', $locale);
                }
                return;
            }
            array_shift($parts);
            $host = implode('.', $parts);
        } else {
            $host = $request->getHost();
        }

        $locale = $request->getPreferredLanguage($this->manager->getAvailableLanguageCodes());
        $url = sprintf('http://%s.%s%s', $locale, $host, $request->getRequestUri());
        $response = new RedirectResponse($url);

        $preferredLanguage = $this->getRequestPreferredLanguage($request);
        if ($preferredLanguage && $locale != $preferredLanguage) {
            $allLanguageCodes = array_keys($this->manager->getLanguages());
            if (in_array($preferredLanguage, $allLanguageCodes)) {
                $session->setFlash('locale_missing', $preferredLanguage);
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
