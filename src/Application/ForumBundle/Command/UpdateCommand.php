<?php

namespace Application\ForumBundle\Command;

use Symfony\Component\Console\Input;

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use DoctrineExtensions\Sluggable\SlugGenerator;

/**
 * Update all topics and categories
 */
class UpdateCommand extends Command
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('forum:update')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $topics = $this->container->get('forum.repository.topic')->findAll();
        $topicUpdater = $this->container->get('forum.updater.topic');
        foreach($topics as $topic) {
            $output->writeLn(sprintf('Topic %s', $topic->getSubject()));
            $topicUpdater->update($topic);
        }
        $dm->flush();

        $categorys = $this->container->get('forum.repository.category')->findAll();
        $categoryUpdater = $this->container->get('forum.updater.category');
        foreach($categorys as $index => $category) {
            $output->writeLn(sprintf('Category %d %s', $index, $category->getName()));
            $category->setPosition($index);
            $categoryUpdater->update($category);
        }
        $dm->flush();

        $output->writeLn('Done');
    }
}
