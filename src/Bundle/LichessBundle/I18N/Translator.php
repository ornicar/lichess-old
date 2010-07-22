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
     * Debug mode
     *
     * @var boolean
     */
    protected $isDebug = null;

    /**
     * Instanciate a translator
     **/
    public function __construct(array $locales, $isDebug = false)
    {
        $this->locales = $locales;
        $this->isDebug = $isDebug;
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

    public function _($text, array $parameters = array(), $locale = null)
    {
        return $this->translate($text, $parameters, $locale);
    }

    public function getMessages($locale)
    {
        if(!isset($this->messages[$locale])) {
            if(!$this->messages[$locale] = $this->getMessagesCache($locale)) {
                if(!$this->messages[$locale] = Yaml::load(realpath(__DIR__.sprintf('/../Resources/i18n/%s.yml', $locale)))) {
                    throw new \InvalidArgumentException(sprintf('The locale "%s" does not exist', $locale));
                }
                $this->setMessagesCache($locale, $this->messages[$locale]);
            }
        }
        return $this->messages[$locale];
    }

    protected function getMessagesCache($locale)
    {
        if($this->isDebug) {
            return false;
        }

        return apc_fetch('lichess.translator.messages.'.$locale);
    }

    protected function setMessagesCache($locale, array $messages)
    {
        if($this->isDebug) {
            return false;
        }

        apc_store('lichess.translator.messages.'.$locale, $this->messages[$locale]);
    }
    
    /**
     * Get isDebug
     * @return boolean
     */
    public function getIsDebug()
    {
      return $this->isDebug;
    }
    
    /**
     * Set isDebug
     * @param  boolean
     * @return null
     */
    public function setIsDebug($isDebug)
    {
      $this->isDebug = $isDebug;
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
