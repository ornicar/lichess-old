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

        $total = $collection->count(array('next' => array('$type' => 3)));
        $batchSize = 10000;
        $it = 0;

        $output->writeLn(sprintf('Found %d games to process', $total));

        for($it = 0, $itMax = ceil($total/$batchSize); $it<$itMax; $it++) {
            $cursor = $collection->find(array('next' => array('$type' => 3)), array('next' => true))->limit($batchSize)->skip($it*$batchSize);
            $games = iterator_to_array($cursor);
            foreach ($games as $id => $game) {
                $nextId = $game['next']['$id'];
                if (0 === $collection->count(array('_id' => $nextId))) {
                    $collection->update(
                        array('_id' => $id),
                        array('$unset' => array('next' => true)),
                        array('safe' => true)
                    );
                    print 'x';
                }
            }
            $output->writeLn(sprintf('%d/%d', ($it+1)*$batchSize, $total));
        }
        $output->writeLn('Done');
    }
}
