<?php

namespace Bundle\LichessBundle\Chess;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Helper\TextHelper;

class Messenger
{
    public function addPlayerMessage(Player $player, $message)
    {
        return $this->addMessage($player->getGame(), $player->getColor(), $message);
    }

    public function addSystemMessage(Game $game, $message)
    {
        return $this->addMessage($game, 'system', $message);
    }

    protected function addMessage(Game $game, $author, $message)
    {
        $author = (string) $author;
        $message = (string) $message;
        if('' === $message) {
            throw new \InvalidArgumentException('Messenger: Can not add empty message');
        }
        if(mb_strlen($message) > 140) {
            throw new \InvalidArgumentException('Messenger: message is too long');
        }
        $game->getRoom()->addMessage($author, $message);
        $htmlMessage = TextHelper::autoLink(htmlentities($message, ENT_COMPAT, 'UTF-8'));
        $sayEvent = array(
            'type' => 'message',
            'html' => sprintf('<li class="%s">%s</li>', $author, $htmlMessage)
        );
        foreach($game->getPlayers() as $player) {
            $player->addEventToStack($sayEvent);
        }
    }
}
