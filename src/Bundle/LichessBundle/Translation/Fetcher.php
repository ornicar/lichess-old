<?php

namespace Bundle\LichessBundle\Translation;

class Fetcher
{
    protected $domain;
    protected $path = '/translate/export.json';
    protected $protocol = 'http://';

    public function __construct($domain)
    {
        $this->domain = trim($domain, '/');
    }

    public function fetch()
    {
        $url = $this->getUrl();
        $json = file_get_contents($url);
        $translations = json_decode($json, true);

        return count($translations);
    }

    public function getUrl()
    {
        return $this->protocol.$this->domain.$this->path;
    }
}
