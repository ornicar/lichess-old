<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Ai\AiInterface;
use Bundle\LichessBundle\Cheat\InternalDetector;
use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Stack;
use Exception;

class Mover
{
    protected $manipulatorFactory;
    protected $clientUpdater;
    protected $synchronizer;
    protected $ai;
    protected $cheatDetector;
    protected $finisher;
    protected $logger;

    public function __construct(ManipulatorFactory $manipulatorFactory, ClientUpdater $clientUpdater, Synchronizer $synchronizer, AiInterface $ai, InternalDetector $cheatDetector, Finisher $finisher, Logger $logger)
    {
        $this->manipulatorFactory = $manipulatorFactory;
        $this->clientUpdater      = $clientUpdater;
        $this->synchronizer       = $synchronizer;
        $this->ai                 = $ai;
        $this->cheatDetector      = $cheatDetector;
        $this->finisher           = $finisher;
        $this->logger             = $logger;
    }

    public function move(Player $player, $version, array $data)
    {
        $this->synchronizer->setAlive($player);
        if(!$player->isMyTurn()) {
            throw new LogicException($this->logger->formatPlayer($player, 'Player:move - not my turn'));
        }
        $game            = $player->getGame();
        $opponent        = $player->getOpponent();
        $isGameAbortable = $game->getIsAbortable();
        $canOfferDraw    = $player->canOfferDraw();
        $move            = $data['from'].' '.$data['to'];
        $options         = isset($data['options']) ? $data['options'] : array();
        $stack           = new Stack();
        $manipulator     = $this->manipulatorFactory->create($game, $stack);

        // perform move and increase game turn
        $opponentPossibleMoves = $manipulator->play($move, $options);

        $player->addEventsToStack($stack->getEvents());
        $player->addEventToStack(array('type' => 'possible_moves', 'possible_moves' => null));

        $eventsSinceClientVersion = $this->clientUpdater->getEventsSinceClientVersion($player, $version);

        if($opponent->getIsAi()) {
            if(!empty($opponentPossibleMoves)) {
                $this->performAiAnswer($player);
            }
        } else {
            $opponent->addEventsToStack($stack->getEvents());
            $opponent->addEventToStack(array('type' => 'possible_moves', 'possible_moves' => $opponentPossibleMoves));
            $this->detectCheat($game);
        }
        if($game->getIsFinished()) {
            $this->finisher->finish($game);
            $this->logger->notice($player, 'Player:move finish');
        }
        if($isGameAbortable != $game->getIsAbortable() || $canOfferDraw != $player->canOfferDraw()) {
            $game->addEventToStacks(array('type' => 'reload_table'));
        }

        return $eventsSinceClientVersion;
    }

    /**
     * After the player has moved, run the AI to get the next move applied immediatly
     *
     * @param Player $player
     * @return void
     */
    protected function performAiAnswer(Player $player)
    {
        $game        = $player->getGame();
        $opponent    = $player->getOpponent();
        $stack       = new Stack();
        $manipulator = $this->manipulatorFactory->create($game, $stack);

        $possibleMoves = $manipulator->play($this->ai->move($game, $opponent->getAiLevel()));

        $player->addEventsToStack($stack->getEvents());
        $player->addEventToStack(array('type' => 'possible_moves', 'possible_moves' => $possibleMoves));
    }

    /**
     * Detect if someone uses an AI to cheat on this game, and act
     *
     * @param Player $player
     * @return void
     */
    protected function detectCheat(Game $game)
    {
        if($cheater = $this->cheatDetector->detectCheater($game)) {
            $game->setStatus(Game::CHEAT);
            $game->setWinner($cheater->getOpponent());
            $game->addEventToStacks(array('type' => 'end'));
        }
    }
}
