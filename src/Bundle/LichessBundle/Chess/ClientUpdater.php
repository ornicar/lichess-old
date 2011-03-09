<?php

namespace Bundle\LichessBundle\Chess;

use OutOfBoundsException;
use Symfony\Component\Routing\Router;
use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Twig\LichessExtension as TwigExtension;
use Bundle\LichessBundle\Document\Player;

class ClientUpdater
{
    protected $synchronizer;
    protected $urlGenerator;
    protected $logger;
    protected $twigExtension;

    public function __construct(Synchronizer $synchronizer, Router $router, Logger $logger, TwigExtension $twigExtension)
    {
        $this->synchronizer  = $synchronizer;
        $this->urlGenerator  = $router->getGenerator();
        $this->logger        = $logger;
        $this->twigExtension = $twigExtension;
    }

    public function getEventsSinceClientVersion(Player $player, $clientVersion, $withPrivateEvents = true)
    {
        $game                = $player->getGame();
        $version             = $player->getStack()->getVersion();
        $isOpponentConnected = $this->synchronizer->isConnected($player->getOpponent());
        $currentPlayerColor  = $game->getTurnColor();
        try {
            $events = $version != $clientVersion ? $this->synchronizer->getDiffEvents($player, $clientVersion) : array();
        } catch(OutOfBoundsException $e) {
            $this->logger->warn($player, 'Player:syncData OutOfBounds');
            $events = array(array('type' => 'redirect', 'url' => $this->urlGenerator->generate('lichess_player', array('id' => $player->getFullId()))));
        }

        // remove private events if user is spectator
        if (!$withPrivateEvents) {
            foreach($events as $index => $event) {
                if('message' === $event['type'] || 'redirect' === $event['type']) {
                    unset($events[$index]);
                }
            }
        }

        // render system messages
        foreach($events as $index => $event) {
            if('message' === $event['type']) {
                $events[$index]['html'] = $this->twigExtension->roomMessage($event['message']);
                unset($events[$index]['message']);
            }
        }

        $data = array('v' => $version, 'o' => $isOpponentConnected, 'e' => $events, 'p' => $currentPlayerColor, 't' => $game->getTurns());
        if($game->hasClock()) {
            $data['c'] = $game->getClock()->getRemainingTimes();
        }

        return $data;
    }
}
