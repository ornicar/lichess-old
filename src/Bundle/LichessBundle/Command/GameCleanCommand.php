<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Bundle\LichessBundle\Document\Game;

/**
 * Remove old not started games
 */
class GameCleanCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('lichess:clean')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $persistence = $this->container->get('lichess_persistence');
        $output->writeLn(sprintf('Will process %d games', $persistence->getNbGames()));
        $time = time() - 60 * 60 * 24 * 7;
        $query = array('turns' => array('$lt' => 3), 'upd' => array('$lt' => $time));
        $output->writeLn(sprintf('Will remove %d games', $persistence->getCollection()->count($query)));
        $persistence->getCollection()->remove($query, array('safe' => true));
        $output->writeLn(sprintf('Done. %d remaining games', $persistence->getNbGames()));
    }
}
