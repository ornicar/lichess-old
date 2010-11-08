<?php

namespace Bundle\LichessBundle\Helper;

use Symfony\Component\Templating\Helper\HelperInterface;
use Symfony\Component\HttpFoundation\Session;

class LocaleHelper implements HelperInterface
{
    /**
     * @var Session
     */
    protected $session;
    protected $locales;
    protected $charset = 'UTF-8';

    public function __construct(Session $session, array $locales)
    {
        $this->session = $session;
        $this->locales = $locales;
    }

    public function getLocale()
    {
        return $this->session->getLocale();
    }

    public function getLocaleName()
    {
        return $this->locales[$this->getLocale()];
    }

    public function getLocales()
    {
        return $this->locales;
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
