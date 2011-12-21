<?php

namespace Lichess\OpeningBundle\Sync;

use Bundle\LichessBundle\Chess\GameEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Lichess\OpeningBundle\Messenger;

class Bot
{
    public function __construct(EventDispatcherInterface $dispatcher, UrlGeneratorInterface $urlGenerator, Messenger $messenger)
    {
        $this->dispatcher = $dispatcher;
        $this->urlGenerator = $urlGenerator;
        $this->messenger = $messenger;
    }

    public function onStart(GameEvent $event)
    {
        $game = $event->getGame();
        $url = $this->urlGenerator->generate('lichess_game', array('id' => $game->getId()));
        $text = implode(" vs ", array_map(function($player) {
            return $player->getUsernameWithElo();
        }, $game->getPlayers()->toArray()));
        $this->messenger->send("[bot]", sprintf('<a href="%s">%s</a>', $url, $text));
    }

    public function onFinish(GameEvent $event)
    {
        //$this->messenger->send("[lichess]",
    }
}
