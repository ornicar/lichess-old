<?php

namespace Bundle\LichessBundle\Starter;

use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Stack;
use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Form\GameConfig;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Seek\SeekQueue;
use Bundle\LichessBundle\Chess\Synchronizer;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\Session;

class AnybodyStarter implements StarterInterface
{
    protected $generator;
    protected $playerBlamer;
    protected $objectManager;
    protected $logger;
    protected $seekQueue;
    protected $session;

    public function __construct(Generator $generator, PlayerBlamer $playerBlamer, DocumentManager $objectManager, Logger $logger, SeekQueue $seekQueue, Synchronizer $synchronizer, Session $session)
    {
        $this->generator = $generator;
        $this->playerBlamer = $playerBlamer;
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->seekQueue = $seekQueue;
        $this->synchronizer = $synchronizer;
        $this->session = $session;
    }

    public function start(GameConfig $config, $color)
    {
        if($this->session) {
            $this->session->set('lichess.game_config.anybody', $config->toArray());
        }
        $queue = $this->seekQueue;
        $result = $queue->add($config->variants, $config->times, $config->increments, $config->modes, $this->session->get('lichess.session_id'), $color);
        $game = $result['game'];
        if(!$game) {
            return null;
        }
        if($result['status'] === $queue::FOUND) {
            if(!$this->synchronizer->isConnected($game->getCreator())) {
                $this->objectManager->remove($game);
                $this->objectManager->flush(array('safe' => true));
                $this->logger->notice($game, 'Game:inviteAnybody remove');
                return null;
            }
            $this->logger->notice($game, 'Game:inviteAnybody join');
            return $game;
        }
        $this->logger->notice($game, 'Game:inviteAnybody queue');

        return $game->getCreator();
    }
}
