<?php

namespace Bundle\LichessBundle\Service;

use Bundle\LichessBundle\Model;
use Bundle\LichessBundle\Chess\Manipulator;

class Board extends Service
{
    public function move($id, $from, $to, $options, $version)
    {
        $player = $this->findPlayer($id);
        $this->container->get('lichess_synchronizer')->setAlive($player);
        $game = $player->getGame();
        if(!$player->isMyTurn()) {
            throw new \LogicException(sprintf('Player:move not my turn game:%s', $game->getId()));
        }
        $opponent = $player->getOpponent();
        $stackClass = $this->container->getParameter('lichess.model.stack.class');
        $stack = new $stackClass();
        $manipulator = new Manipulator($game, $stack);
        $manipulator->setContainer($this->container);
        $opponentPossibleMoves = $manipulator->play($from . ' ' . $to, $options);
        $player->addEventsToStack($stack->getEvents());
        $player->addEventToStack(array('type' => 'possible_moves', 'possible_moves' => null));

        $return = $this->getPlayerSyncData($player, $version);

        if($opponent->getIsAi()) {
            if(!empty($opponentPossibleMoves)) {
                $stack->reset();
                $ai = $this->container->get('lichess_ai');
                try {
                    $possibleMoves = $manipulator->play($ai->move($game, $opponent->getAiLevel()));
                }
                catch(\Exception $e) {
                    $this->container->get('logger')->err(sprintf('Player:move Crafty game:%s, variant:%s, turn:%d - %s %s', $game->getId(), $game->getVariantName(), $game->getTurns(), get_class($e), $e->getMessage()));
                    $ai = $this->container->get('lichess_ai_fallback');
                    $possibleMoves = $manipulator->play($ai->move($game, $opponent->getAiLevel()));
                }
                $player->addEventsToStack($stack->getEvents());
                $player->addEventToStack(array('type' => 'possible_moves', 'possible_moves' => $possibleMoves));
            }
        }
        else {
            $opponent->addEventsToStack($stack->getEvents());
            $opponent->addEventToStack(array('type' => 'possible_moves', 'possible_moves' => $opponentPossibleMoves));
            // Detect if someone uses an AI to cheat on this game, and act
            if($cheater = $this->container->get('lichess.anticheat')->detectCheater($game)) {
                $game->setStatus(Game::CHEAT);
                $game->setWinner($cheater->getOpponent());
                $cheater->addEventToStack(array('type' => 'end'));
                $cheater->getOpponent()->addEventToStack(array('type' => 'end'));
            }
        }
        if($game->getIsFinished()) {
            $this->container->get('lichess_finisher')->finish($game);
            $this->container->get('logger')->notice(sprintf('Player:move finish game:%s, %s', $game->getId(), $game->getStatusMessage()));
        }
        $this->container->get('lichess.object_manager')->flush();

        return $return;
    }
}