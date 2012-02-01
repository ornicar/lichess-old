<?php

namespace Bundle\LichessBundle\Sync;

use OutOfBoundsException;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Renderer\RoomMessageRenderer;

class ClientUpdater
{
    protected $memory;
    protected $roomMessageRenderer;

    public function __construct(Memory $memory, RoomMessageRenderer $roomMessageRenderer)
    {
        $this->memory              = $memory;
        $this->roomMessageRenderer = $roomMessageRenderer;
    }

    public function getEventsSinceClientVersion(Player $player, $clientVersion, $withPrivateEvents)
    {
        $game                = $player->getGame();
        $version             = $player->getStackVersion();
        $opponentActivity    = $this->memory->getActivity($player->getOpponent());
        $currentPlayerColor  = $game->getTurnColor();

        try {
            $events = $version != $clientVersion ? $this->getDiffEvents($player, $clientVersion) : array();
        } catch (OutOfBoundsException $e) {
            return array('reload' => true);
        }

        // remove private events if user is spectator
        if (!$withPrivateEvents) {
            foreach($events as $index => $event) {
                if('message' === $event['type'] || 'redirect' === $event['type']) {
                    unset($events[$index]);
                }
            }
        } else {
            // render room messages
            foreach($events as $index => $event) {
                if('message' === $event['type']) {
                    $events[$index]['html'] = $this->roomMessageRenderer->renderRoomMessage($event['message']);
                    unset($events[$index]['message']);
                }
            }
        }

        $data = array('v' => $version, 'oa' => $opponentActivity, 'e' => array_values($events), 'p' => $currentPlayerColor, 't' => $game->getTurns());
        if($game->hasClock()) {
            $data['c'] = $game->getClock()->getRemainingTimes();
        }

        return $data;
    }

    protected function getDiffEvents(Player $player, $clientVersion)
    {
        $playerStack = $player->getStack();
        $stackVersion = $playerStack->getVersion();
        if($stackVersion === $clientVersion) {
            return array();
        }
        if(!$playerStack->hasVersion($clientVersion)) {
            throw new OutOfBoundsException(sprintf('ClientUpdater:OutOfBound player=%d requested=%s', $stackVersion, $clientVersion));
        }
        $events = array();
        for($version = $clientVersion+1; $version <= $stackVersion; $version++) {
            $events[] = $playerStack->getEvent($version);
        }

        return $events;
    }
}
