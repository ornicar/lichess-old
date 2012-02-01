<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Component\Console\Input;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Bundle\LichessBundle\Document\Room;

/**
 * Rename Game.next references where the corresponding game no longer exists
 */
class GameMoveRoomCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('lichess:game:move-room')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->getContainer()->get('lichess.repository.game');
        $roomRepo = $this->getContainer()->get('lichess.repository.room');
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $collection = $dm->getDocumentCollection($repo->getDocumentName())->getMongoCollection();
        $roomCollection = $dm->getDocumentCollection($roomRepo->getDocumentName())->getMongoCollection();

        $batchSize = 1000;
        $it = 0;

        $cursor = $collection->find(array('room' => array('$exists' => 1)), array('room' => true));
        do {
            $games = array();
            for($i = 0; $i < $batchSize; $i++) {
                $games[] = $cursor->getNext();
            }
            $done = empty($games) || $games[0] == null;
            if (!$done) {
                $rooms = array();
                $ids = array();
                foreach ($games as $game) {
                    $id = (string) $game['_id'];
                    $ids[] = $id;
                    if (!empty($game['room']['messages'])) {
                        $rooms[] = array('_id' => $id, 'messages' => $game['room']['messages']);
                    }
                }
                if (!empty($rooms)) $roomCollection->batchInsert($rooms, array('safe' => true));
                if (!empty($ids)) $collection->update(array('_id' => array('$in' => $ids)), array('$unset' => array('room' => true)), array('multiple' => true, 'safe' => true));
                unset($rooms, $games, $ids);
                $output->writeLn(sprintf('%d', ($it++)*$batchSize));
            }
        } while(!$done);
        $output->writeLn('Done');
    }
}
