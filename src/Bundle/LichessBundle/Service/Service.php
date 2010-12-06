<?php

namespace Bundle\LichessBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Bundle\LichessBundle\Model;

abstract class Service {

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function cachePlayerVersions(Model\Game $game)
    {
        $storage = $this->container->get('lichess_storage');

        foreach($game->getPlayers() as $player) {
            if(!$player->getIsAi()) {
                $storage->store($game->getId().'.'.$player->getColor().'.data', $player->getStack()->getVersion(), 3600);
            }
        }
    }

    protected function cleanPlayerVersionsCache(Model\Game $game)
    {
        $storage = $this->container->get('lichess_storage');

        foreach($game->getPlayers() as $player) {
            $storage->delete($game->getId().'.'.$player->getColor().'.data');
        }
    }

    /**
     * Get the player for this id
     *
     * @param string $id
     * @return Player
     */
    public function findPlayer($id)
    {
        $gameId = substr($id, 0, 8);
        $playerId = substr($id, 8, 12);

        $game = $this->container->get('lichess.repository.game')->findOneById($gameId);
        if(!$game) {
            throw new \Exception('Player:findPlayer Can\'t find game '.$gameId);
        }

        $player = $game->getPlayerById($playerId);
        if(!$player) {
            throw new \Exception('Player:findPlayer Can\'t find player '.$playerId);
        }

        return $player;
    }

    /**
     * Get the public player for this id
     *
     * @param string $id
     * @return Player
     */
    public function findPublicPlayer($id, $color)
    {
        $game = $this->container->get('lichess.repository.game')->findOneById($id);
        if(!$game) {
            throw new \Exception('Player:findPublicPlayer Can\'t find game '.$id);
        }

        $player = $game->getPlayer($color);
        if(!$player) {
            throw new \Exception('Player:findPublicPlayer Can\'t find player '.$color);
        }

        return $player;
    }

    protected function getPlayerSyncData(Model\Player $player, $clientVersion)
    {
        $game = $player->getGame();
        $version = $player->getStack()->getVersion();
        $isOpponentConnected = $this->container->get('lichess_synchronizer')->isConnected($player->getOpponent());
        $currentPlayerColor = $game->getTurnColor();
        try {
            $events = $version != $clientVersion ? $this->container->get('lichess_synchronizer')->getDiffEvents($player, $clientVersion) : array();
        }
        catch(\OutOfBoundsException $e) {
            $this->container->get('logger')->warn(sprintf('Player:syncData OutOfBounds game:%s', $game->getId()));
            $events = array(array('type' => 'redirect', 'url' => $this->container->get('router')->generate('lichess_player', array('id' => $player->getFullId()))));
        }
        // render system messages
        foreach($events as $index => $event) {
            if('message' === $event['type']) {
                $events[$index]['html'] = $this->container->get('templating.helper.lichess')->roomMessage($event['message']);
                unset($events[$index]['message']);
            }
        }

        $data = array('v' => $version, 'o' => $isOpponentConnected, 'e' => $events, 'p' => $currentPlayerColor, 't' => $game->getTurns());
        $data['ncp'] = $this->container->get('lichess_synchronizer')->getNbConnectedPlayers();
        if($game->hasClock()) {
            $data['c'] = $game->getClock()->getRemainingTimes();
        }

        return $data;
    }
}