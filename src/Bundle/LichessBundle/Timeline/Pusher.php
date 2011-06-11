<?php

namespace Bundle\LichessBundle\Timeline;

use Bundle\LichessBundle\Document\Game;

class Pusher extends AbstractPusher
{
    public function pushMate(Game $game)
    {
        $entry = $this->templating->render('LichessBundle:Timeline:mateEntry.html.twig', array(
            'game' => $game
        ));
        $this->timeline->add('game_mate', $entry, $game->getWinner()->getUser());
    }
}
