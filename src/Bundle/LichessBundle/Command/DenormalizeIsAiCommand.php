<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Denormalizes Game.isAi
 */
class DenormalizeIsAiCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('lichess:game:denormalize-is-ai')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->getContainer()->get('lichess.repository.game');
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $query = array('isAi' => array('$exists' => false));
        $select = array('players.isAi' => true);
        $collection = $dm->getDocumentCollection($repo->getDocumentName())->getMongoCollection();

        $total = $collection->count($query);
        $batchSize = 10000;
        $it = 0;

        $output->writeLn(sprintf('Found %d games to process', $total));

        for($it = 0, $itMax = ceil($total/$batchSize); $it<$itMax; $it++) {
            $cursor = $collection->find($query, $select)->limit($batchSize)->skip($it*$batchSize);
            $games = iterator_to_array($cursor);
            $nbIsAi = 0;
            foreach ($games as $id => $game) {
                if (!empty($game['players'][0]['isAi']) || !empty($game['players'][1]['isAi'])) {
                    $collection->update(
                        array('_id' => $id),
                        array('$set' => array('isAi' => true)),
                        array('safe' => true)
                    );
                    $nbIsAi++;
                }
            }
            $output->writeLn(sprintf('%d%% %d/%d %d/%d', ($it+1)*$batchSize*100/$total, ($it+1)*$batchSize, $total, $nbIsAi, $batchSize));
            sleep(5);
        }
        $output->writeLn('Done');
    }
}
