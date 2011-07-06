<?php

namespace Bundle\LichessBundle\Starter;

use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Stack;
use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Config\GameConfig;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Seek\SeekQueue;
use Bundle\LichessBundle\Sync\Memory;
use Bundle\LichessBundle\Config\Persistence;

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
    protected $configPersistence;
    protected $checkCreatorIsActive;

    public function __construct(Generator $generator, PlayerBlamer $playerBlamer, DocumentManager $objectManager, Logger $logger, SeekQueue $seekQueue, Memory $memory, Persistence $configPersistence, Session $session, $checkCreatorIsActive)
    {
        $this->generator            = $generator;
        $this->playerBlamer         = $playerBlamer;
        $this->objectManager        = $objectManager;
        $this->logger               = $logger;
        $this->seekQueue            = $seekQueue;
        $this->memory               = $memory;
        $this->session              = $session;
        $this->configPersistence    = $configPersistence;
        $this->checkCreatorIsActive = (bool) $checkCreatorIsActive;
    }

    public function start(GameConfig $config)
    {
        $this->configPersistence->saveConfigFor('anybody', $config->toArray());
        $queue = $this->seekQueue;
        $result = $queue->add($config->getVariants(), $config->getTimes(), $config->getIncrements(), $config->getModes(), $this->getSessionId());
        $game = $result['game'];
        if(!$game) {
            return null;
        }
        if($result['status'] === $queue::FOUND) {
            if($this->checkCreatorIsActive && !$this->isGameCreatorActive($game)) {
                $this->objectManager->remove($game);
                $this->objectManager->flush(array('safe' => true));
                $this->logger->notice($game, 'Game:inviteAnybody remove');
                // try again
                return $this->start($config);
            }
            $this->logger->notice($game, 'Game:inviteAnybody join');
            return $game;
        }
        $this->logger->notice($game, 'Game:inviteAnybody queue');
        $game->setConfigArray($config->toArray());

        return $game->getCreator();
    }

    public function cancel(Player $player)
    {
        $this->logger->notice($player, 'Game:inviteAnybody cancel');
        $this->seekQueue->remove($player->getGame());
    }

    protected function isGameCreatorActive(Game $game)
    {
        return 2 == $this->memory->getActivity($game->getCreator());
    }

    protected function getSessionId()
    {
        return $this->session->get('lichess.session_id');
    }
}
