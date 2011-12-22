<?php

namespace Lichess\OpeningBundle\Sync;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Lichess\OpeningBundle\Messenger;
use Bundle\LichessBundle\Document\Game;

class Bot
{
    public function __construct(UrlGeneratorInterface $urlGenerator, Messenger $messenger)
    {
        $this->urlGenerator = $urlGenerator;
        $this->messenger = $messenger;
    }

    public function onStart(Game $game)
    {
		if (!$game->hasUser()) {
            return;
        }
        $url = $this->urlGenerator->generate('lichess_game', array('id' => $game->getId()));
        $text = implode(" vs ", array_map(function($player) {
            return $player->getUsernameWithElo();
        }, $game->getPlayers()->toArray()));

        return $this->messenger->send("[bot]", sprintf('<a href="%s">%s</a>', $url, $text));
    }
}
