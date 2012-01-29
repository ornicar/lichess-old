<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Ai\AiInterface;
use Bundle\LichessBundle\Cheat\InternalDetector;
use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use ArrayObject;
use Bundle\LichessBundle\Sync\Memory;
use LogicException;
use InvalidArgumentException;

class Mover
{
    protected $manipulatorFactory;
    protected $memory;
    protected $ai;
    protected $cheatDetector;
    protected $finisher;
    protected $logger;

    public function __construct(ManipulatorFactory $manipulatorFactory, Memory $memory, AiInterface $ai, InternalDetector $cheatDetector, Finisher $finisher, Logger $logger)
    {
        $this->manipulatorFactory = $manipulatorFactory;
        $this->memory             = $memory;
        $this->ai                 = $ai;
        $this->cheatDetector      = $cheatDetector;
        $this->finisher           = $finisher;
        $this->logger             = $logger;
    }

    public function move(Player $player, array $data)
    {
        $this->memory->setAlive($player);

        if (empty($data['from']) || empty($data['to'])) {
            throw new InvalidArgumentException('Mover::move Invalid data received, from and to are required.');
        }
        if (!$player->getGame()->getIsPlayable()) {
            throw new LogicException($this->logger->formatPlayer($player, 'Player:move - game is already finished'));
        }
        if(!$player->isMyTurn()) {
            throw new LogicException($this->logger->formatPlayer($player, 'Player:move - not my turn'));
        }

        $game            = $player->getGame();
        $opponent        = $player->getOpponent();
        $isGameAbortable = $game->getIsAbortable();
        $canOfferDraw    = $player->canOfferDraw();
        $move            = $data['from'].' '.$data['to'];
        $options         = isset($data['options']) ? $data['options'] : array();
        $events           = new ArrayObject();
        $manipulator     = $this->manipulatorFactory->create($game, $events);

        // increment player blur
        if (!empty($data['b']) && 1 == intval($data['b'])) {
            $game->incrementBlurs($player->getColor());
        }

        // perform move and increase game turn
        $opponentPossibleMoves = $manipulator->play($move, $options);

        $player->addEventToStack(array('type' => 'possible_moves', 'possible_moves' => null));
        $player->addEventsToStack($events->getArrayCopy());

        if($opponent->getIsAi()) {
            if(!empty($opponentPossibleMoves)) {
                $this->performAiAnswer($player);
            }
        } else {
            $opponent->addEventToStack(array('type' => 'possible_moves', 'possible_moves' => $opponentPossibleMoves));
            $opponent->addEventsToStack($events->getArrayCopy());
            $this->detectCheat($game);
        }
        if($game->getIsFinished()) {
            $this->finisher->finish($game);
            $this->logger->notice($player, 'Player:move finish');
        }
        if($isGameAbortable != $game->getIsAbortable() || $canOfferDraw != $player->canOfferDraw()) {
            $game->addEventToStacks(array('type' => 'reload_table'));
        }
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
        $events       = new ArrayObject();
        $manipulator = $this->manipulatorFactory->create($game, $events);

        $possibleMoves = $manipulator->play($this->ai->move($game, $opponent->getAiLevel()));

        $player->addEventToStack(array('type' => 'possible_moves', 'possible_moves' => $possibleMoves));
        $player->addEventsToStack($events->getArrayCopy());
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
