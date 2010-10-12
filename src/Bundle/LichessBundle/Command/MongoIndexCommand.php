<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Proves things.
 */
class MongoIndexCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('lichess:mongo:index')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $persistence = $this->container->get('lichess_persistence');
        $persistence->ensureIndexes();
    }
}
