<?php

namespace Application\ForumBundle\Command;

use Symfony\Component\Console\Input;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use DoctrineExtensions\Sluggable\SlugGenerator;

/**
 * Remove all posts and topics from an authorName
 */
class RemoveAllFromAuthorCommand extends ContainerAwareCommand
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
        $authorNames = array_map('trim', (array) explode(',', $input->getArgument('authorName')));

        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $postRemover = $this->getContainer()->get('forum.remover.post');
        $topicRemover = $this->getContainer()->get('forum.remover.topic');

        if ($authorNames[0] === 'http') {
            $posts = $this->getContainer()->get('forum.repository.post')->createQueryBuilder()
                ->field('authorName')->equals(new \MongoRegex('/^http\:\//'))
                ->getQuery()
                ->execute();
            $output->writeLn(sprintf('Will remove %d posts http:', $posts->count()));

            foreach ($posts as $post) {
                try {
                    if ($post->getNumber() == 1) {
                        $topicRemover->remove($post->getTopic());
                    } else {
                        $postRemover->remove($post);
                    }
                } catch (\Exception $e) {
                    $output->writeLn($post->getId().' '.$e->getMessage());
                }
            }
            $dm->flush();
            $output->writeLn('Done');
            return;
        }

        foreach ($authorNames as $authorName) {
            $posts = $this->getContainer()->get('forum.repository.post')->findBy(array(
                'authorName' => $authorName
            ));
            $output->writeLn(sprintf('Will remove %d posts from %s', $posts->count(), $authorName));

            foreach ($posts as $post) {
                try {
                    if ($post->getNumber() == 1) {
                        $topicRemover->remove($post->getTopic());
                    } else {
                        $postRemover->remove($post);
                    }
                } catch (\Exception $e) {
                    $output->writeLn($e->getMessage());
                }
            }
            $dm->flush();
            $dm->clear();
        }

        $output->writeLn('Done');
    }
}
