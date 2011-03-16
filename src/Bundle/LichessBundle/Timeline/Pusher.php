<?php

namespace Bundle\LichessBundle\Timeline;

use Bundle\LichessBundle\Document\TimelineEntryRepository;
use Bundle\LichessBundle\Document\Game;
use Symfony\Component\Templating\EngineInterface as Templating;

class Pusher
{
    protected $timeline;
    protected $templating;

    public function __construct(TimelineEntryRepository $timeline, Templating $templating)
    {
        $this->timeline      = $timeline;
        $this->templating    = $templating;
    }

    public function pushMate(Game $game)
    {
        $entry = $this->templating->render('LichessBundle:Timeline:mateEntry.html.twig', array(
            'game' => $game
        ));
        $this->timeline->add('game_mate', $entry, $game->getWinner()->getUser());
    }
}
