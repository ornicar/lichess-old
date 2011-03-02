<?php

namespace Bundle\LichessBundle\Starter;

use Bundle\LichessBundle\Ai\AiInterface;
use Bundle\LichessBundle\Blamer\PlayerBlamer;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Stack;
use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Form\GameConfig;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Document\Clock;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\Session;

class FriendStarter implements StarterInterface
{
    protected $generator;
    protected $playerBlamer;
    protected $ai;
    protected $objectManager;
    protected $logger;
    protected $session;

    public function __construct(Generator $generator, PlayerBlamer $playerBlamer, DocumentManager $objectManager, Logger $logger, Session $session = null)
    {
        $this->generator     = $generator;
        $this->playerBlamer  = $playerBlamer;
        $this->objectManager = $objectManager;
        $this->logger        = $logger;
        $this->session       = $session;
    }

    public function start(GameConfig $config)
    {
        if($this->session) {
            $this->session->set('lichess.game_config.friend', $config->toArray());
        }
        $color = $config->resolveColor();
        $player = $this->generator->createGameForPlayer($color, $config->variant);
        $this->playerBlamer->blame($player);
        $game = $player->getGame();
        if($config->time) {
            $clock = new Clock($config->time * 60, $config->increment);
            $game->setClock($clock);
        }
        $game->setIsRated($config->mode);
        $this->objectManager->persist($game);
        $this->objectManager->flush(array('safe' => true));
        $this->logger->notice($game, 'Game:inviteFriend create');

        return $player;
    }
}
