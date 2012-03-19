<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Elo\Calculator;
use Bundle\LichessBundle\Elo\Updater;
use Bundle\LichessBundle\Logger;
use Bundle\LichessBundle\Cheat\Judge;
use LogicException;
use Bundle\LichessBundle\Lila;

class Finisher
{
    protected $calculator;
    protected $messenger;
    protected $eloUpdater;
    protected $logger;
    protected $judge;
    protected $autoDraw;

    public function __construct(Calculator $calculator, Messenger $messenger, Lila $lila, Updater $eloUpdater, Logger $logger, Judge $judge, AutoDraw $autoDraw)
    {
        $this->calculator = $calculator;
        $this->messenger  = $messenger;
        $this->lila     = $lila;
        $this->eloUpdater = $eloUpdater;
        $this->logger     = $logger;
        $this->judge      = $judge;
        $this->autoDraw   = $autoDraw;
    }

    public function finish(Game $game, $status = null, Player $winner = null)
    {
        // try to prevent concurrent game finishing
        $apcLockKey = 'game_finish_lock_' . $game->getId();
        if (apc_fetch($apcLockKey)) { throw new FinisherException('Game finish lock'); }
        apc_store($apcLockKey, true, 60);

        if ($status) $game->setStatus($status);
        if ($winner) $game->setWinner($winner);

        $this->messenger->addSystemMessage($game, $game->getStatusMessage());

        $this->judge->study($game);

        $this->updateElo($game);

        $this->updateNbGames($game);

        $game->finish();
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
        if ($oftPlayer = $game->checkOutOfTime()) {
            if ($this->autoDraw->hasTooFewMaterialToMate($oftPlayer->getOpponent())) {
                $winner = null;
            } else {
                $winner = $oftPlayer->getOpponent();
            }
            $this->finish($game, Game::OUTOFTIME, $winner);
            $events = array(array('type' => 'end'), array('type' => 'possible_moves', 'possible_moves' => null));
            $game->addEventsToStacks($events);
            $this->logger->notice($player, 'Player:outoftime');
            return true;
        } else {
            throw new FinisherException($this->logger->formatPlayer($player, 'Player:outoftime too early or not applicable'));
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
        if($game->getIsPlayable() && 0 == $this->lila->getActivity($player->getOpponent())) {
            $this->finish($game, Game::TIMEOUT, $player);
            $game->addEventToStacks(array('type' => 'end'));
            $this->logger->notice($player, 'Player:forceResign');
        }
        else {
            $this->logger->warn($player, 'Player:forceResign FAIL');
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
            $this->finish($game, Game::DRAW, null);
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
        $this->finish($game, Game::ABORTED, null);
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

        $this->finish($game, Game::RESIGN, $opponent);
    }

    protected function updateNbGames(Game $game)
    {
        foreach ($game->getPlayers() as $player) {
            if ($user = $player->getUser()) {
                $user->setNbGames($user->getNbGames() + 1);
                if ($game->getIsRated()) {
                    $user->setNbRatedGames($user->getNbRatedGames() + 1);
                }
            }
        }
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
        if ($whiteUser->is($blackUser)) {
            return;
        }
        if($winner = $game->getWinner()) {
            $win = $winner->isWhite() ? -1 : 1;
        } else {
            $win = 0;
        }
        list($whiteElo, $blackElo) = $this->calculator->calculate($whiteUser, $blackUser, $win);
        $white->setEloDiff($whiteEloDiff = $whiteElo - $whiteUser->getElo());
        $black->setEloDiff($blackEloDiff = $blackElo - $blackUser->getElo());

        $this->eloUpdater->updateElo($whiteUser, $whiteElo, $game);
        $this->eloUpdater->updateElo($blackUser, $blackElo, $game);

        $this->logger->notice($game, sprintf('Elo exchanged: %s', $whiteEloDiff));
    }
}
