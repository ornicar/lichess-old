<?php

namespace Bundle\LichessBundle\Helper;

use Bundle\LichessBundle\Translation\Manager;
use Symfony\Component\Templating\Helper\HelperInterface;
use Symfony\Component\HttpFoundation\Session;

class LocaleHelper implements HelperInterface
{
    /**
     * @var Session
     */
    protected $session;
    protected $manager;
    protected $locales;
    protected $charset = 'UTF-8';

    public function __construct(Session $session, Manager $manager)
    {
        $this->session = $session;
        $this->manager = $manager;
    }

    public function getLocale()
    {
        return $this->session->getLocale();
    }

    public function getLocaleName()
    {
        return $this->manager->getAvailableLanguageName($this->getLocale());
    }

    public function getLocales()
    {
        return $this->manager->getAvailableLanguages();
    }

    /**
     * Get locales which are not the current locale
     *
     * @return array
     **/
    public function getOtherLocales()
    {
        $locales = $this->getLocales();
        unset($locales[$this->getLocale()]);
        ksort($locales);

        return $locales;
    }

    public function getName()
    {
        return 'locale';
    }

    /**
     * Sets the default charset.
     *
     * @param string $charset The charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * Gets the default charset.
     *
     * @return string The default charset
     */
    public function getCharset()
    {
        return $this->charset;
    }
}
