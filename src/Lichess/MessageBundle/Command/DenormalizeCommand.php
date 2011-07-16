<?php

namespace Lichess\MessageBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class DenormalizeCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('lichess:message:denormalize')
            ->setDescription('Applies denormalization on messages')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $threadClass = 'Lichess\MessageBundle\Document\Thread';
        $manager = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $repository = $manager->getRepository($threadClass);
        $threads = $repository->findAll();

        $output->writeln(sprintf('%d threads to denormalize', $count = $threads->count()));
        foreach ($threads as $thread) {
            $thread->denormalize();
        }
        $output->writeln('Flushing...');
        $manager->flush();
        $output->writeln('Done');
    }
}
