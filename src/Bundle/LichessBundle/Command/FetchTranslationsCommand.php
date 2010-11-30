<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Fetch translations from remote server and creates Git branches for each of them
 */
class FetchTranslationsCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('start', InputArgument::REQUIRED, 'Translation ID to start with'),
            ))
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Clear all translation branches')
            ->setName('lichess:translation:fetch')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fetcher = $this->container->get('lichess.translation.fetcher');
        $fetcher->setLogger(function($message) use ($output)
        {
            $output->writeLn(sprintf('<info>%s</info>', $message));
        });
        if($input->getOption('clear')) {
            $output->writeLn(sprintf('Will clear translations'));
            $nb = $fetcher->clear();
        }
        $output->writeLn(sprintf('Will fetch translations starting from %d, from remote "%s"', $input->getArgument('start'), $fetcher->getUrl()));
        $translations = $fetcher->fetch($input->getArgument('start'));
        $output->writeLn(sprintf('%d translations fetched', count($translations)));
    }
}
