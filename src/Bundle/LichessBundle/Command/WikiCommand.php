<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpKernel\Util\Filesystem;
use Symfony\Component\Finder\Finder;
use Bundle\LichessBundle\Document\WikiPage;

class WikiCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('lichess:wiki')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $repoUrl = 'git://github.com/ornicar/lichess.wiki.git';
        //$repoUrl = '/home/thib/lichess.wiki/';
        $repoName = 'lichess_wiki';
        $repoDir = '/tmp';
        $repo = $repoDir . '/' . $repoName;
        $fs->remove($repo);
        $command = sprintf('cd %s && git clone %s %s', $repoDir, $repoUrl, $repoName);
        system($command);
        $finder = new Finder();
        $mdFiles = $finder->files()->depth(0)->name('*.md')->in($repo);
        $pages = array();
        foreach ($mdFiles as $mdFile) {
            $name = preg_replace('/^(.+)\.md$/', '$1', $mdFile->getFileName());
            if ($name == "Home") continue;
            $output->writeLn('* ' . $name);
            $command = sprintf('sundown %s/%s', $repo, $mdFile->getFileName());
            exec($command, $out);
            $body = implode($out, "\n");
            unset($out);
            $pages[] = new WikiPage($name, $body);
        }
        if (empty($pages)) {
            throw new \Exception('No pages to save');
        }
        $this->getContainer()->get('lichess.repository.wiki_page')->replaceWith($pages);
        $this->getContainer()->get('doctrine.odm.mongodb.document_manager')->flush();
        $output->writeLn('Flushed');
    }
}
