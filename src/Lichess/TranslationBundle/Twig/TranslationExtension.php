<?php

namespace Lichess\TranslationBundle\Twig;

use Lichess\TranslationBundle\TranslationManager;
use Symfony\Component\HttpFoundation\Request;

class TranslationExtension extends \Twig_Extension
{
    protected $manager;

    /**
     * Constructor.
     *
     * @param TranslationManager $manager
     */
    public function __construct(TranslationManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
            'lichess_locales' => new \Twig_Function_Method($this, 'getLocales', array('is_safe' => array('html'))),
            'lichess_locale_name' => new \Twig_Function_Method($this, 'getLocaleName', array('is_safe' => array('html'))),
            'lichess_locale_url' => new \Twig_Function_Method($this, 'getLocaleUrl', array('is_safe' => array('html')))
        );
    }

    public function getLocales(Request $request)
    {
        $url = $this->getLocaleUrl('{{locale}}', $request);
        $availableLocales = $this->manager->getAvailableLanguages();
        unset($availableLocales[$request->getSession()->getLocale()]);
        ksort($availableLocales);
        $locales = array();
        foreach ($availableLocales as $code => $name) {
            $locales[str_replace('{{locale}}', $code, $url)] = $name;
        }

        return $locales;
    }

    public function getLocaleUrl($locale, Request $request)
    {
        $host = $request->getHost();
        // remove locale from the host name
        $host = substr($host, strpos($host, '.')+1);

        return sprintf('http://%s.%s%s', $locale, $host, $request->getRequestUri());
    }

    public function getLocaleName($locale)
    {
        return $this->manager->getAvailableLanguageName($locale);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'lichess_translation';
    }
}
