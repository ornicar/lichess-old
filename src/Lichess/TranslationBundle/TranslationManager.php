<?php

namespace Lichess\TranslationBundle;

use Symfony\Component\Yaml\Yaml;
use InvalidArgumentException;

class TranslationManager
{
    protected $languages;
    protected $availableLanguages;
    protected $referenceLanguage;
    protected $translationDir;

    public function __construct($referenceLanguage, array $availableLanguages, $translationDir)
    {
        $this->referenceLanguage = $referenceLanguage;
        $this->availableLanguages = $availableLanguages;
        $this->translationDir = $translationDir;
    }

    public function getTranslationStatus($code)
    {
        if($available = $this->isAvailable($code)) {
            $keys = array_keys(array_filter($this->getMessages($code)));
            $name = $this->getAvailableLanguageName($code);
        } else {
            $keys = array();
            $name = $this->getLanguageName($code);
        }
        $reference = $this->getMessageKeys();
        $diff = array_diff($reference, $keys);
        $missing = count($diff);
        $percent = floor(100 * (count($keys) / count($reference)));
        if ($missing && $percent == 100) {
            $percent = 99;
        }
        return array(
            'name' => $name,
            'missing' => $missing,
            'percent' => $percent,
            'available' => $available
        );
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
        $existing = $this->getMessages($code);
        $messages = array();
        foreach($this->getMessageKeys() as $key) {
            $messages[$key] = isset($existing[$key]) ? $existing[$key] : '';
        }

        return $messages;
    }

    public function sortMessages(array $messages)
    {
        $keys = $this->getMessageKeys();
        $sorted = array();
        foreach($keys as $key) {
            if(!empty($messages[$key])) {
                $sorted[$key] = $messages[$key];
            }
        }

        return $sorted;
    }

    public function getMessages($code)
    {
        $file = $this->translationDir.'/messages.'.$code.'.yml';
        if(file_exists($file)) {
            return Yaml::parse($file);
        }
        throw new InvalidArgumentException(sprintf('No messages for language "%s"', $code));
    }

    public function saveMessages($code, array $messages)
    {
        $file = $this->getLanguageFile($code);
        $lines = array();
        foreach($messages as $from => $to) {
            if(!empty($to)) {
                $lines[] = sprintf('"%s": "%s"', $from, $to);
            }
        }

        $yaml = implode("\n", $lines)."\n";
        file_put_contents($file, $yaml);
    }

    public function getLanguageFile($code)
    {
        return $this->translationDir.'/messages.'.$code.'.yml';
    }

    public function getLanguages()
    {
        if(null === $this->languages) {
            $this->languages = include(__DIR__.'/Resources/locales.php');
            foreach($this->languages as $code => $name) {
                $this->languages[$code] = $code.' - '.$name;
            }
        }

        return $this->languages;
    }

    public function isAvailable($code)
    {
        return isset($this->availableLanguages[$code]);
    }

    public function getAvailableLanguages()
    {
        return $this->availableLanguages;
    }

    public function getAvailableLanguageName($code)
    {
        return $this->availableLanguages[$code];
    }

    public function getAvailableLanguageCodes()
    {
        return array_keys($this->availableLanguages);
    }

    public function getLanguageName($code)
    {
        $languages = $this->getLanguages();

        return isset($languages[$code]) ? $languages[$code] : $code;
    }

}
