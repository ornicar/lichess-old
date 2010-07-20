<?php

namespace Bundle\LichessBundle\I18N;
use Symfony\Components\Yaml\Yaml;

/**
 * A translator used for translating text.
 */
class Translator
{
    /**
     * List of available locales
     *
     * @var array
     */
    protected $locales = array();

    /**
     * Default translation locale
     *
     * @var string
     */
    protected $locale = 'en';

    /**
     * Message catalogues
     *
     * @var array
     */
    protected $messages = array();

    /**
     * Instanciate a translator
     **/
    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * Translates a given text string.
     *
     * @param string $text        The text to translate
     * @param array  $parameters  The parameters to inject into the text
     * @param string $locale      The locale of the translated text. If null,
     *                            the preconfigured locale of the translator
     *                            or the system's default culture is used.
     */
    public function translate($text, array $parameters = array(), $locale = null)
    {
        if(null === $locale) {
            $locale = $this->locale;
        }

        if(!array_key_exists($locale, $this->locales)) {
            throw new \InvalidArgumentException(sprintf('The locale "%s" is not available', $locale));
        }

        // replace object with strings
        foreach ($parameters as $key => $value)
        {
            if (is_object($value) && method_exists($value, '__toString'))
            {
                $parameters[$key] = $value->__toString();
            }
        }


        if('en' !== $locale) {
            $messages = $this->getMessages($locale);
            if(isset($messages[$text])) {
                $text = $messages[$text];
            } 
        }

        $text = strtr($text, $parameters);

        return $text;
    }

    protected function getMessages($locale)
    {
        if(!isset($this->messages[$locale])) {
            if(!$this->messages[$locale] = apc_fetch('lichess.translator.messages.'.$locale)) {
                if(!$this->messages[$locale] = Yaml::load(realpath(__DIR__.sprintf('/../Resources/i18n/%s.yml', $locale)))) {
                    throw new \InvalidArgumentException(sprintf('The locale "%s" does not exist', $locale));
                }
                apc_store('lichess.translator.messages.'.$locale, $this->messages[$locale]);
            }
        }
        return $this->messages[$locale];
    }

    /**
     * Get locales
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Set locales
     * @param  array
     * @return null
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    /**
     * Get locale
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    public function getLocaleName()
    {
        return $this->locales[$this->locale];
    }

    /**
     * Set locale
     * @param  string
     * @return null
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
}
