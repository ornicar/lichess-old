<?php

namespace Bundle\LichessBundle\Translation;
use Symfony\Component\Yaml\Yaml;

class Manager
{
    protected $languages;

    public function __construct()
    {
        $this->languages = include(__DIR__.'/../Resources/config/locales.php');
        ksort($this->languages);
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
