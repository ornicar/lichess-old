<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Elo\Calculator;
use Bundle\LichessBundle\Elo\Updater;
use LogicException;

class Finisher
{
    protected $calculator;
    protected $messenger;
    protected $synchronizer;
    protected $eloUpdater;
    protected $logger;

    public function __construct(Calculator $calculator, Messenger $messenger, Synchronizer $synchronizer, Updater $eloUpdater, Logger $logger)
    {
        $this->calculator   = $calculator;
        $this->messenger    = $messenger;
        $this->synchronizer = $synchronizer;
        $this->eloUpdater   = $eloUpdater;
        $this->logger       = $logger;
    }

    public function finish(Game $game)
    {
        $this->messenger->addSystemMessage($game, $game->getStatusMessage());
        $this->updateElo($game);
    }

    /**
     * Ends the game if out of time
     *
     * @param Game $game
     * @return void
     */
    public function outoftime(Player $player)
    {
        $game = $player->getGame();
        if($game->checkOutOfTime()) {
            $this->finish($game);
            $events = array(array('type' => 'end'), array('type' => 'possible_moves', 'possible_moves' => null));
            $game->addEventsToStacks($events);
            $this->logger->notice($player, 'Player:outoftime');
            return true;
        } else {
            $this->logger->warn($player, 'Player:outoftime too early or not applicable');
            throw new FinisherException();
        }
    }

    /**
     * Resign this player opponent if possible
     *
     * @param Player $player
     * @return void
     */
    public function forceResign(Player $player)
    {
        $game = $player->getGame();
        if($game->getIsPlayable() && $this->synchronizer->isTimeout($player->getOpponent())) {
            $game->setStatus(Game::TIMEOUT);
            $game->setWinner($player);
            $this->finish($game);
            $game->addEventToStacks(array('type' => 'end'));
            $this->logger->notice($player, 'Player:forceResign');
        }
        else {
            $this->logger->warn($player, 'Player:forceResign');
        }
    }

    /**
     * The player draws the game
     *
     * @param Player $player
     * @return void
     */
    public function claimDraw(Player $player)
    {
        $game = $player->getGame();
        if($game->getIsPlayable() && $game->isThreefoldRepetition() && $player->isMyTurn()) {
            $game->setStatus(Game::DRAW);
            $this->finish($game);
            $game->addEventToStacks(array('type' => 'end'));
            $this->logger->notice($player, 'Player:claimDraw');
        }
        else {
            $this->logger->warn($player, 'Player:claimDraw FAIL');
        }
    }

    /**
     * The player aborts the game
     *
     * @param Player $player
     * @return void
     */
    public function abort(Player $player)
    {
        $game = $player->getGame();
        if(!$game->getIsAbortable()) {
            $this->logger->warn($player, 'Player:abort non-abortable');
            throw new FinisherException();
        }
        $game->setStatus(Game::ABORTED);
        $this->finish($game);
        $game->addEventToStacks(array('type' => 'end'));
        $this->logger->notice($player, 'Player:abort');
    }

    /**
     * The player resigns and loses the game
     *
     * @param Player $player
     * @return void
     */
    public function resign(Player $player)
    {
        $game = $player->getGame();
        if(!$game->isResignable()) {
            $this->logger->warn($player, 'Player:resign non-resignable');
            throw new FinisherException();
        }
        $opponent = $player->getOpponent();

        $game->setStatus(Game::RESIGN);
        $game->setWinner($opponent);
        $this->finish($game);
        $game->addEventToStacks(array('type' => 'end'));
        $this->logger->notice($player, 'Player:resign');
    }

    protected function updateElo(Game $game)
    {
        // Game can be aborted
        if(!$game->getIsFinished()) {
            return;
        }
        if(!$game->getIsRated()) {
            return;
        }
        // Don't rate games with less than 2 moves
        if($game->getTurns() < 2) {
            return;
        }
        $white = $game->getPlayer('white');
        $black = $game->getPlayer('black');
        $whiteUser = $white->getUser();
        $blackUser = $black->getUser();
        // Don't rate games when one ore more player is not registered
        if(!$whiteUser || !$blackUser) {
            return;
        }
        if($winner = $game->getWinner()) {
            $win = $winner->isWhite() ? -1 : 1;
        } else {
            $win = 0;
        }
        list($whiteElo, $blackElo) = $this->calculator->calculate($white->getElo(), $black->getElo(), $win);
        $white->setEloDiff($whiteEloDiff = $whiteElo - $white->getElo());
        $black->setEloDiff($blackElo - $black->getElo());

        $this->eloUpdater->updateElo($whiteUser, $whiteElo, $game);
        $this->eloUpdater->updateElo($blackUser, $blackElo, $game);

        $this->logger->notice($game, sprintf('Elo exchanged: %s', $whiteEloDiff));
    }
}
