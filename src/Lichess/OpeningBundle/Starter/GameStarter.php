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

        if($game->getInvited()->getIsAi()) {
            return array();
        }

        $messages = array();
        $messages[] = $this->messenger->addSystemMessage($game, ucfirst($game->getCreator()->getColor()).' creates the game');
        $messages[] = $this->messenger->addSystemMessage($game, ucfirst($game->getInvited()->getColor()).' joins the game');
        if($game->hasClock()) {
            $messages[] = $this->messenger->addSystemMessage($game, 'Clock: '.$game->getClock()->getName());
        }
        if($game->getIsRated()) {
            $messages[] = $this->messenger->addSystemMessage($game, 'This game is rated');
        }

        return array_map(function(array $mes) { return $mes['message']; }, $messages);
    }
}
