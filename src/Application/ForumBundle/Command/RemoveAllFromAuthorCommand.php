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
 * Remove all posts and topics from an authorName
 */
class RemoveAllFromAuthorCommand extends Command
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('authorName', InputArgument::REQUIRED, 'The author name'),
            ))
            ->setName('forum:remove-author')
            ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $authorName = $input->getArgument('authorName');

        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $postRemover = $this->container->get('forum.remover.post');
        $topicRemover = $this->container->get('forum.remover.topic');

        $posts = $this->container->get('forum.repository.post')->findBy(array(
            'authorName' => $authorName
        ));
        $output->writeLn(sprintf('Will remove %d posts from %s', $posts->count(), $authorName));

        foreach ($posts as $post) {
            try {
                $postRemover->remove($post);
            } catch (\LogicException $e) {
                try {
                    $topicRemover->remove($post->getTopic());
                } catch (\LogicException $e) {
                    $output->writeLn($e->getMessage());
                }
            }
        }
        $dm->flush();

        $output->writeLn('Done');
    }
}
