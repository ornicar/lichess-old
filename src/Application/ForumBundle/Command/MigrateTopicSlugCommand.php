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
 * Add Topic.slug
 */
class MigrateTopicSlugCommand extends Command
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('forum:migrate:topic-slug')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->container->get('forum.repository.topic');
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $topics = $repo->createQueryBuilder()
            ->field('slug')->exists(false)
            ->getQuery()
            ->execute();

        if($topics->count()) {
            $generator = new SlugGenerator($dm);
            $output->writeLn(sprintf('%d topics to process', $topics->count()));
            foreach($topics as $topic) {
                $generator->process($topic);
                $output->writeLn($topic->getSlug());
                $dm->flush();
            }
        }
        $output->writeLn('Done');
    }
}
