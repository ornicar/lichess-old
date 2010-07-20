<?php

namespace Bundle\LichessBundle\Helper;

use Symfony\Components\Templating\Helper\HelperInterface;
use Bundle\LichessBundle\I18N\Translator;

class TranslatorHelper implements HelperInterface
{
    /**
     * @var Translator
     */
    protected $translator;
    protected $charset = 'UTF-8';

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function translate($text, $parameters = array(), $locale = null)
    {
        return $this->translator->translate($text, $parameters, $locale);
    }

    public function getLocale()
    {
        return $this->translator->getLocale();
    }

    public function getLocaleName()
    {
        return $this->translator->getLocaleName();
    }

    public function getLocales()
    {
        return $this->translator->getLocales();
    }

    /**
     * Get locales which are not the current locale
     *
     * @return array
     **/
    public function getOtherLocales()
    {
        $locales = $this->translator->getLocales();
        unset($locales[$this->translator->getLocale()]);

        return $locales;
    }

    public function getName()
    {
        return 'translator';
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
