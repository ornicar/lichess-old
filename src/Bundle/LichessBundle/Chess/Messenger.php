<?php

namespace Bundle\LichessBundle\Chess;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Twig\LichessExtension;

class Messenger
{
    protected $helper;

    public function __construct(LichessExtension $helper)
    {
        $this->helper = $helper;
    }

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
        if($game->addRoomMessage($author, $message)) {
            $sayEvent = array(
                'type' => 'message',
                'message' => array($author, $message)
            );
            foreach($game->getPlayers() as $player) {
                $player->addEventToStack($sayEvent);
            }
        }
    }
}
