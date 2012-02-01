<?php

namespace Lichess\OpeningBundle\Starter;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Chess\Messenger;

class GameStarter
{
    protected $messenger;

    public function __construct(Messenger $messenger)
    {
        $this->messenger = $messenger;
    }

    public function start(Game $game)
    {
        $game->start();

        $this->messenger->addSystemMessage($game, ucfirst($game->getCreator()->getColor()).' creates the game');
        $this->messenger->addSystemMessage($game, ucfirst($game->getInvited()->getColor()).' joins the game');
        if($game->hasClock()) {
            $this->messenger->addSystemMessage($game, 'Clock: '.$game->getClock()->getName());
        }
        if($game->getIsRated()) {
            $this->messenger->addSystemMessage($game, 'This game is rated');
        }
    }
}
