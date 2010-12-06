<?php

namespace Bundle\LichessBundle\Service;

use Bundle\LichessBundle\Model;

class Player extends Service
{
    public function checkOutOfTime($id, $version)
    {
        $player = $this->findPlayer($id);
        $opponent = $player->getOpponent();
        $game = $player->getGame();

        if($game->checkOutOfTime()) {
            $this->container->get('lichess_finisher')->finish($game);
            $events = array(array('type' => 'end'), array('type' => 'possible_moves', 'possible_moves' => null));
            $player->addEventsToStack($events);
            $opponent->addEventsToStack($events);
            $this->container->get('lichess.object_manager')->flush();
            $this->container->get('logger')->notice(sprintf('Player:outoftime game:%s', $game->getId()));
        }

        $this->container->get('logger')->warn(sprintf('Player:outoftime finished game:%s', $game->getId()));

        return $this->getPlayerSyncData($player, $version);
    }

    public function rematch($id)
    {
        $player = $this->findPlayer($id);
        $opponent = $player->getOpponent();
        $game = $player->getGame();

        if(!$game->getIsFinished()) {
            $this->container->get('logger')->warn(sprintf('Player:rematch not finished game:%s', $game->getId()));
            return $player;
        }

        if($nextPlayerId = $game->getNext()) {
            $nextOpponent = $this->findPlayer($nextPlayerId);

            if($nextOpponent->getColor() === $player->getColor()) {
                $nextGame = $nextOpponent->getGame();
                $nextPlayer = $nextOpponent->getOpponent();
                if(!$nextGame->getIsStarted()) {
                    // @todo is this the best way?
                    $roomClass = $this->container->getParameter('lichess.model.room.class');
                    $nextGame->setRoom(new $roomClass($game->getRoom()->getMessages()));
                    
                    if($game->hasClock()) {
                        // @todo is this the best way?
                        $clockClass = $this->container->getParameter('lichess.model.clock.class');
                        $nextGame->setClock(new $clockClass($game->getClock()->getLimit(), $game->getClock()->getMoveBonus()));
                    }

                    $nextGame->start();
                    $opponent->addEventToStack(array('type' => 'redirect', 'url' => $this->container->get('router')->generate('lichess_player', array('id' => $nextOpponent->getFullId()))));
                    $this->container->get('lichess.object_manager')->flush();
                    if($this->container->get('lichess_synchronizer')->isConnected($opponent)) {
                        $this->container->get('lichess_synchronizer')->setAlive($nextOpponent);
                    }
                    $this->container->get('logger')->notice(sprintf('Player:rematch join game:%s', $nextGame->getId()));
                } else {
                    $this->container->get('logger')->warn(sprintf('Player:rematch join already started game:%s', $nextGame->getId()));
                }
                return $nextPlayer;
            }
        } else {
            $nextPlayer = $this->container->get('lichess_generator')->createReturnGame($player);
            $this->container->get('lichess.object_manager')->persist($nextPlayer->getGame());
            $opponent->addEventToStack(array('type' => 'reload_table'));
            $this->container->get('lichess_synchronizer')->setAlive($player);
            $this->container->get('logger')->notice(sprintf('Player:rematch proposal for game:%s', $game->getId()));
            $this->container->get('lichess.object_manager')->flush();
            $this->cachePlayerVersions($player->getGame());
        }

        return $player;
    }

    public function resign($id, $force = false)
    {
        $player = $this->findPlayer($id);

        if ($force) {
            $game = $player->getGame();
            if(!$game->getIsFinished() && $this->container->get('lichess_synchronizer')->isTimeout($player->getOpponent())) {
                $game->setStatus(Model\Game::TIMEOUT);
                $game->setWinner($player);
                $this->container->get('lichess_finisher')->finish($game);
                $player->addEventToStack(array('type' => 'end'));
                $player->getOpponent()->addEventToStack(array('type' => 'end'));
                $this->container->get('lichess.object_manager')->flush();
                $this->container->get('logger')->notice(sprintf('Player:forceResign game:%s', $game->getId()));
            }
            else {
                $this->container->get('logger')->warn(sprintf('Player:forceResign FAIL game:%s', $game->getId()));
            }
        } else {
            $game = $player->getGame();
            if($game->getIsFinished()) {
                $this->container->get('logger')->warn(sprintf('Player:resign finished game:%s', $game->getId()));
                return false;
            }
            $opponent = $player->getOpponent();

            $game->setStatus(Model\Game::RESIGN);
            $game->setWinner($opponent);
            $this->container->get('lichess_finisher')->finish($game);
            $player->addEventToStack(array('type' => 'end'));
            $opponent->addEventToStack(array('type' => 'end'));
            $this->container->get('lichess.object_manager')->flush();
            $this->container->get('logger')->notice(sprintf('Player:resign game:%s', $game->getId()));
        }
    }

    public function sync($id, $color, $alive, $version)
    {
        $player = $this->findPublicPlayer($id, $color);
        
        if ($alive) {
            $this->container->get('lichess_synchronizer')->setAlive($player);
        }

        $this->cachePlayerVersions($player->getGame());

        return $this->getPlayerSyncData($player, $version);
    }

    public function setAiLevel($id, $level)
    {
        $player = $this->findPlayer($id);
        $level = min(8, max(1, $level));
        $player->getOpponent()->setAiLevel($level);
        $this->container->get('lichess.object_manager')->flush();
    }

    public function addMessage($id, $message, $version)
    {
        $player = $this->findPlayer($id);
        $this->container->get('lichess_synchronizer')->setAlive($player);
        $this->container->get('lichess.messenger')->addPlayerMessage($player, $message);
        $this->container->get('lichess.object_manager')->flush();

        return $this->getPlayerSyncData($player, $version);
    }
}