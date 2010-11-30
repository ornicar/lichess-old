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
    protected $logger;

    public function __construct(Manager $manager, $domain)
    {
        $this->manager = $manager;
        $this->domain = trim($domain, '/');
    }

    public function setLogger(\Closure $closure)
    {
        $this->logger = $closure;
    }

    public function getLogger()
    {
        return $this->logger ?: function() {};
    }

    public function fetch($start)
    {
        $logger = $this->getLogger();
        $url = sprintf('%s?start=%d', $this->getUrl(), $start);
        $json = file_get_contents($url);
        $translations = json_decode($json, true);
        if(empty($translations)) {
            $logger('No translation fetched');
            return;
        }
        ksort($translations);

        $repo = $this->createGitRepo();
        $currentBranch = $repo->getCurrentBranch();
        if('master' != $currentBranch) {
            $repo->git('checkout master');
        }
        foreach($translations as $id => $translation) {
            $branchName = sprintf('t/%d-%s', $id, $translation['code']);
            if(!$repo->hasBranch($branchName)) {
                $commitMessage = sprintf('commit -m "%d (%s) by %s on %s, %d messages"',
                    $id,
                    $this->manager->getLanguageName($translation['code']),
                    $translation['author'] ?: 'Anonymous',
                    $translation['date'],
                    count($translation['messages'])
                );
                $logger($commitMessage);
                $repo->git('checkout -b '.$branchName);
                $this->manager->saveMessages($translation['code'], $translation['messages']);
                $repo->git('add '.$this->manager->getLanguageFile($translation['code']));
                $repo->git($commitMessage);
                $repo->git('checkout master');
            }
        }
        $repo->git('checkout '.$currentBranch);

        return count($translations);
    }

    public function clear()
    {
        $repo = $this->createGitRepo();
        $currentBranch = $repo->getCurrentBranch();
        if('master' != $currentBranch) {
            $repo->git('checkout master');
        }
        $logger = $this->getLogger();
        foreach($repo->getBranches() as $branch) {
            if(preg_match('#^t/\d+#', $branch)) {
                $logger(sprintf('Remove branch %s', $branch));
                $repo->git('branch -D '.$branch);
            }
        }
    }

    public function getUrl()
    {
        return $this->protocol.$this->domain.$this->path;
    }

    protected function createGitRepo()
    {
        return new phpGitRepo(__DIR__.'/../../../..', false);
    }
}
