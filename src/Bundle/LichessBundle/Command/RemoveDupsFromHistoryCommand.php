<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Remove occurences of a game from a cluttered history
 */
class RemoveDupsFromHistoryCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('lichess:history:remove-dups')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $histories = $this->container->get('lichess.repository.history')->findAll();

        foreach ($histories as $history) {
            $entries = $history->getEntries();
            $newEntries = array();
            $gameIds = array();
            foreach ($entries as $index => $entry) {
                if (isset($entry['g'])) {
                    $gameId = $entry['g'];
                    if (!isset($gameIds[$gameId])) {
                        $gameIds[$gameId] = true;
                        $newEntries[$index] = $entry;
                    }
                } else {
                    $newEntries[$index] = $entry;
                }
            }

            if (count($entries) != count($newEntries)) {
                $output->writeLn(sprintf('Remove %d entries from %s history', count($entries) - count($newEntries), $history->getId()));
            }
                $history->setEntries($newEntries);
        }

        $this->container->get('doctrine.odm.mongodb.document_manager')->flush();
    }
}
