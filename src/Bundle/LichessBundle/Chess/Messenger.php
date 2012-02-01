<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\RoomRepository;
use Bundle\LichessBundle\Document\Room;

class Messenger
{
    protected $repo;

    public function __construct(RoomRepository $repo)
    {
        $this->repo = $repo;
    }

    public function addPlayerMessage(Player $player, $message)
    {
        return $this->addMessage($player->getGame(), $player->getColor(), $message);
    }

    public function addSystemMessage(Game $game, $message)
    {
        return $this->addMessage($game, 'system', $message);
    }

    /** returns bool success */
    public function addMessage(Game $game, $author, $message)
    {
        if($game->getInvited()->getIsAi()) {
            return false;
        }
        $room = $this->repo->findOneByGameOrCreate($game);
        $author = (string) $author;
        $message = (string) $message;
        if('' === $message) {
            throw new \InvalidArgumentException('Messenger: Can not add empty message');
        }
        if(mb_strlen($message) > 140) {
            throw new \InvalidArgumentException('Messenger: message is too long');
        }
        $room->addMessage($author, $message);
        $sayEvent = array(
            'type' => 'message',
            'message' => array($author, $message)
        );
        foreach($game->getPlayers() as $player) {
            $player->addEventToStack($sayEvent);
        }

        return true;
    }
}
