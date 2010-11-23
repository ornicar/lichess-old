<?php

namespace Bundle\LichessBundle\Translation;
use Symfony\Component\Yaml\Yaml;

class Manager
{
    protected $languages;
    protected $referenceLanguage;

    public function __construct($referenceLanguage)
    {
        $this->referenceLanguage = $referenceLanguage;
        $this->languages = include(__DIR__.'/../Resources/config/locales.php');
        foreach($this->languages as $code => $name) {
            $this->languages[$code] = $code.' - '.$name;
        }
    }

    public function getMessageKeys()
    {
        return array_keys($this->getMessages($this->referenceLanguage));
    }

    public function getEmptyMessages()
    {
        $messages = array();
        foreach($this->getMessageKeys() as $key) {
            $messages[$key] = '';
        }

        return $messages;
    }

    public function getMessagesWithReferenceKeys($code)
    {
        $messages = $this->getMessages($code);
        $keys = $this->getMessageKeys();
        foreach($messages as $key => $translated) {
            if(!in_array($key, $keys)) {
                unset($messages[$key]);
            }
        }
        foreach($keys as $key) {
            if(!array_key_exists($key, $messages)) {
                $messages[$key] = '';
            }
        }

        return $messages;
    }

    public function getMessages($code)
    {
        $file = __DIR__.'/../Resources/translations/messages.'.$code.'.yml';
        if(file_exists($file)) {
            return Yaml::load($file);
        }
        throw new \InvalidArgumentException();
    }

    public function getLanguages()
    {
        return $this->languages;
    }

    public function getLanguageName($code)
    {
        return $this->languages[$code];
    }

}
