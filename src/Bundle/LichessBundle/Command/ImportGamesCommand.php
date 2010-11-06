<?php

namespace Bundle\LichessBundle\Command;

use Bundle\LichessBundle\Document\Game;
use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Import games from previous format
 */
class ImportGamesCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('lichess:import')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        require_once __DIR__.'/Pieces.php';
        $manager = $this->container->get('lichess.object_manager');
        $this->container->get('lichess.repository.game')->createQuery()->remove()->execute();
        $collection = $manager->getMongo()->getMongo()->selectCollection('lichess', 'game');
        $newCollection = $manager->getDocumentCollection('Bundle\LichessBundle\Document\Game')->getMongoCollection();
        $nbGames = $collection->count(array());
        $nbSteps = ceil($nbGames/100);
        for($step=0; $step<$nbSteps; $step++) {
            $cursor = $collection->find(array())->sort(array('upd' => -1))->limit(100)->skip($step*100);
            $newGames = array();
            foreach($cursor as $gameArray) {
                $game = $this->decodeOldGame($gameArray);
                $newGames[] = $this->transformData($game);
            }
            $newCollection->batchInsert($newGames, array('safe' => true, 'fsync' => true));
            unset($newGames, $cursor);
            $output->writeLn(sprintf('%d/%d', ($step+1)*100, $nbGames));
        }
    }

    protected function transformData($o)
    {
        $data = array(
            '_id' => $o->hash.'00',
            'creatorColor' => $o->creator->color,
            'createdAt' => new \MongoDate($o->upd),
            'updatedAt' => new \MongoDate($o->upd),
            'positionHashes' => $o->positionHashes ?: array(),
            'status' => $o->status,
            'turns' => $o->turns,
            'variant' => isset($o->variant) ? $o->variant : Game::VARIANT_STANDARD
        );
        if(isset($o->pgnMoves)) {
            $data['pgnMoves'] = $o->pgnMoves;
        }
        if(isset($o->next)) {
            $data['next'] = $o->next;
        }
        if(isset($o->initialFen)) {
            $data['initialFen'] = $o->initialFen;
        }
        if(isset($o->clock)) {
            $clock = $o->clock;
            $data['clock'] = array(
                'color' => $clock->color,
                'limit' => $clock->limit,
                'moveBonus' => isset($clock->moveBonus) ? $clock->moveBonus : 5,
                'timer' => $clock->timer,
                'times' => $clock->times
            );
        }
        if(isset($o->room)) {
            $room = $o->room;
            $data['room'] = array('messages' => array());
            foreach($room->messages as $message) {
                if(mb_strlen($message[1])<140) {
                    $data['room']['messages'][] = $message;
                }
            }
        }
        $data['players'] = array();
        foreach($o->players as $p) {
            $player = array(
                'color' => $p->color,
                'id' => $p->hash
            );
            if($p->isAi) {
                $player['isAi'] = true;
                $player['aiLevel'] = $p->aiLevel;
            }
            if($p->isWinner) {
                $player['isWinner'] = true;
            }
            $player['stack'] = array(
                'events' => $p->stack->events ?: array()
            );
            $player['pieces'] = array();
            foreach($p->pieces as $pi) {
                $class = strtolower(get_class($pi));
                $type = 'knight' === $class ? 'n' : $class{0};
                $piece = array(
                    'x' => $pi->x,
                    'y' => $pi->y,
                    't' => $type
                );
                if($pi->isDead) {
                    $piece['isDead'] = true;
                }
                if(null !== $pi->firstMove) {
                    $piece['firstMove'] = $pi->firstMove;
                }
                $player['pieces'][] = $piece;
            }
            $data['players'][] = $player;
        }

        return $data;
    }

    protected function decodeOldGame(array $gameArray)
    {
        // Uncompress
        $game = gzuncompress($gameArray['bin']->bin);
        // Fix Clock
        $game = preg_replace('#39:"\0Bundle\\\LichessBundle\\\Chess\\\Clock\0#', '5:"', $game);
        $game = preg_replace('#43:"\0Bundle\\\LichessBundle\\\Chess\\\Clock\0#', '9:"', $game);
        // Replace Piece classes
        $game = preg_replace_callback('#\d+:"Bundle\\\LichessBundle\\\Entities\\\Piece\\\(\w+)"#', function($matches) {
            return strlen($matches[1]).':"'.$matches[1].'"';
        }, $game);
        // Replace classes with stdClass
        $game = preg_replace('#\d+:"Bundle\\\LichessBundle\\\[^"]+"#', '8:"stdClass"', $game);
        // Make all properties public
        $game = preg_replace_callback('#s:(\d+):"\0\*\0#', function($matches) {
            return 's:'.($matches[1] - 3).':"';
        }, $game);
        $game = unserialize($game);
        $game->upd = $gameArray['upd'];

        return $game;
    }
}
