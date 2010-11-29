<?php

namespace Bundle\LichessBundle\Translation;
use phpGitRepo;

require_once __DIR__.'/../../../vendor/php-git-repo/lib/phpGitRepo.php';

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

        $repo = new phpGitRepo(__DIR__.'/../../../..');
        $currentBranch = $repo->getCurrentBranch();
        $repo->git('stash');
        $repo->git('checkout master');
        foreach($translations as $id => $translation) {
            $branchName = 't/'.$id;
            if(!$repo->hasBranch($branchName)) {
                $repo->git('checkout -b '.$branchName);
                $repo->git('checkout master');
            }
        }
        $repo->git('checkout '.$currentBranch);
        $repo->git('stash pop');

        return count($translations);
    }

    public function getUrl()
    {
        return $this->protocol.$this->domain.$this->path;
    }
}
