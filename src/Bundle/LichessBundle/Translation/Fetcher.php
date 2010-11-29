<?php

namespace Bundle\LichessBundle\Translation;
use phpGitRepo;

require_once __DIR__.'/../../../vendor/php-git-repo/lib/phpGitRepo.php';

class Fetcher
{
    protected $manager;
    protected $domain;
    protected $path = '/translate/export.json';
    protected $protocol = 'http://';

    public function __construct(Manager $manager, $domain)
    {
        $this->manager = $manager;
        $this->domain = trim($domain, '/');
    }

    public function fetch()
    {
        $url = $this->getUrl();
        $json = file_get_contents($url);
        $translations = json_decode($json, true);

        $repo = new phpGitRepo(__DIR__.'/../../../..', true);
        $currentBranch = $repo->getCurrentBranch();
        $repo->git('checkout master');
        foreach($translations as $id => $translation) {
            $branchName = 'translation/'.$id;
            if(!$repo->hasBranch($branchName)) {
                $repo->git('checkout -b '.$branchName);
                $this->manager->saveMessages($translation['code'], $translation['messages']);
                $repo->git('add '.$this->manager->getLanguageFile($translation['code']));
                $repo->git(sprintf('commit -m "%d (%s) by %s on %s, %d messages"',
                    $id,
                    $this->manager->getLanguageName($translation['code']),
                    $translation['author'] ?: 'Anonymous',
                    $translation['date'],
                    count($translation['messages'])
                ));
                $repo->git('checkout master');
            }
        }
        $repo->git('checkout '.$currentBranch);

        return count($translations);
    }

    public function getUrl()
    {
        return $this->protocol.$this->domain.$this->path;
    }
}
