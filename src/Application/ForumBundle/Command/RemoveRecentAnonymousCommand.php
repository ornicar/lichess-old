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
 * Remove all recent posts and topics
 */
class RemoveRecentAnonymousCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('forum:remove-recent')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime('-2 hours');

        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $postRemover = $this->getContainer()->get('herzult_forum.remover.post');
        $topicRemover = $this->getContainer()->get('herzult_forum.remover.topic');

		$posts = $this->getContainer()->get('herzult_forum.repository.post')->createQueryBuilder()
			->field('createdAt')->gt($date)
			->getQuery()
			->execute()
			->toArray();
		$posts = array_filter($posts, function($post) { return !$post->hasAuthor(); });

		$output->writeLn(sprintf('Will remove %d posts', count($posts)));

		foreach ($posts as $post) {
			$output->writeLn(substr($post->getMessage(), 0, 80));
			try {
				if ($post->getNumber() == 1) {
					$topicRemover->remove($post->getTopic());
				} else {
					$postRemover->remove($post);
				}
			} catch (\Exception $e) {
				$output->writeLn($e->getMessage());
			}
			$dm->flush();
		}

        $output->writeLn('Done');
    }
}
