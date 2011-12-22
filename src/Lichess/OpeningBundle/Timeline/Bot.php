<?php

namespace Lichess\OpeningBundle\Timeline;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Lichess\OpeningBundle\Document\EntryRepository;
use Lichess\OpeningBundle\Document\Entry;

class Bot
{
    public function __construct(EntryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function onStart(Game $game)
    {
        if (!$game->hasUser()) {
            return;
        }

        $entry = new Entry(array(
          'players' => array_map(function($player) {
            return array('u' => $player->hasUser() ? $player->getUsername() : null, 'ue' => $player->getUsernameWithElo());
          }, $game->getPlayers()->toArray()),
          'id' => $game->getId(),
          'variant' => $game->getVariantName(),
          'rated' => $game->getIsRated(),
          'clock' => $game->hasClock() ? array($game->getClock()->getLimitInMinutes(), $game->getClock()->getIncrement()) : null
        ));

        $this->repository->add($entry);

        return $entry;
    }
}
