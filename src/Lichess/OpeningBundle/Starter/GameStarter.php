<?php

namespace Lichess\OpeningBundle\Starter;

use Bundle\LichessBundle\Document\Game;

class GameStarter
{
    public function start(Game $game)
    {
        $game->start();

        if($game->getInvited()->getIsAi()) {
            return array();
        }

        $messages = array();
        $messages[] = ucfirst($game->getCreator()->getColor()).' creates the game';
        $messages[] = ucfirst($game->getInvited()->getColor()).' joins the game';
        if($game->hasClock()) {
            $messages[] = 'Clock: '.$game->getClock()->getName();
        }
        if($game->getIsRated()) {
            $messages[] = 'This game is rated';
        }

        return $messages;
    }
}
