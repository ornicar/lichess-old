<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Component\Console\Input;

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Rename Game.Clock.movebonus to Game.Clock.increment
 */
class MigrateClockIncrementCommand extends Command
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('lichess:migrate:clock-increment')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->container->get('lichess.repository.game');
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $collection = $dm->getDocumentCollection($repo->getDocumentName())->getMongoCollection();
        $games = $collection->find(array(
            'clock.moveBonus' => array(
                '$exists' => true
            )
        ), array('clock.moveBonus' => true));

        $output->writeLn(sprintf('Found %d games to process', $games->count()));
        foreach($games as $game) {
            $collection->update(
                array('_id' => $game['_id']),
                array(
                    '$set' => array('clock.increment' => $game['clock']['moveBonus']),
                    '$unset' => array('clock.moveBonus' => true)
                )
            );
        }
        $output->writeLn('Done');
    }
}
