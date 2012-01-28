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
use Bundle\LichessBundle\Chess\Board;

/**
 * Rename Game.next references where the corresponding game no longer exists
 */
class CompressPiecesCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('lichess:game:compress-pieces')
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

        $cursor = $collection->find(array('players.moveTimes' => array('$exists' => 1), 'players.pieces' => array('$exists' => 1)), array('pgnMoves' => true, 'players' => true));

        while($game = $cursor->getNext()) {
            $id = (string) $game['_id'];
            $playerPieces = array();
            $playerMts = array();
            foreach ($game['players'] as $pi => $player) {
                if (empty($player['pieces'])) {
                    var_dump($id);
                    break;
                }
                if (!is_array($player['pieces'])) {
                    var_dump($id);
                    var_dump($player['pieces']);
                    break;
                }
                $ps = array();
                foreach($player['pieces'] as $piece) {
                    $letter = $piece['t'];
                    if (isset($piece['isDead']) && $piece['isDead']) $letter = strtoupper($letter);
                    $fm = isset($piece['firstMove']) ? $piece['firstMove'] : "";
                    $ps[] = Board::keyToPiotr(Board::posToKey($piece['x'], $piece['y'])) . $letter . $fm;
                }
                $playerPieces[$pi] = implode(' ', $ps);
                if (!empty($player['moveTimes'])) {
                    $playerMts[$pi] = implode(' ', $player['moveTimes']);
                }
            }
            if (count($playerPieces) == 2) {
                $update = array(
                    '$unset' => array(
                        'players.0.pieces' => true,
                        'players.1.pieces' => true,
                        'players.0.stack' => true,
                        'players.1.stack' => true,
                        'players.0.moveTimes' => true,
                        'players.1.moveTimes' => true,
                        'positionHashes' => true,
                        'pgnMoves' => true
                    ),
                    '$set' => array(
                        'players.0.ps' => $playerPieces[0],
                        'players.1.ps' => $playerPieces[1],
                        'pgn' => isset($game['pgnMoves']) ? implode(' ', $game['pgnMoves']) : null
                    )
                );
                foreach($playerMts as $pmi => $mts) {
                    $update['$set']["players.$pmi.mts"] = $mts;
                }
                $collection->update(array('_id' => $id), $update, array('multiple' => false, 'safe' => true));
            }

            if (0 == (++$it) % 1000) $output->writeLn($it);
        }
        $output->writeLn('Done');
    }
}
