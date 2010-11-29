<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Fetch games from remote server and creates Git branches for each of them
 */
class FetchGamesCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('lichess:translation:fetch')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fetcher = $this->container->get('lichess.translation.fetcher');
        $output->writeLn(sprintf('Will fetch translations from remote "%s"', $fetcher->getRemoteUrl()));
        $nb = $fetcher->fetch();
        $output->writeLn(sprintf('%d translations fetched', $nb));
    }
}
