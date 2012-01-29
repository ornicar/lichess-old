<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Component\Console\Input;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Rename Game.next references where the corresponding game no longer exists
 */
class GameCleanupNextCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('lichess:game:cleanup-next')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->getContainer()->get('lichess.repository.game');
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $collection = $dm->getDocumentCollection($repo->getDocumentName())->getMongoCollection();

        $it = 0;

        $cursor = $collection->find(array('next' => array('$type' => 3)), array('next' => true));

        while($game = $cursor->getNext()) {
            $nextId = (string) $game['next']['$id'];
            if (0 === $collection->count(array('_id' => $nextId))) {
                $id = (string) $game['_id'];
                $collection->update(
                    array('_id' => $id),
                    array('$unset' => array('next' => true)),
                    array('safe' => true)
                );
                print 'x';
            }
            if (0 == (++$it) % 10000) $output->writeLn("\n".$it);
        }
        $output->writeLn('Done');
    }
}
