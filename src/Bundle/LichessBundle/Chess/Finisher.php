<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Elo\Calculator;
use LogicException;

class Finisher
{
    protected $calculator;
    protected $messenger;
    protected $logger;

    public function __construct(Calculator $calculator, Messenger $messenger, Logger $logger)
    {
        $this->calculator = $calculator;
        $this->messenger  = $messenger;
        $this->logger     = $logger;
    }

    public function finish(Game $game)
    {
        $this->messenger->addSystemMessage($game, $game->getStatusMessage());
        $this->updateElo($game);
    }

    public function outoftime(Game $game)
    {
        if($game->checkOutOfTime()) {
            $this->finish($game);
            $events = array(array('type' => 'end'), array('type' => 'possible_moves', 'possible_moves' => null));
            $game->addEventToStacks($events);
            $this->logger->notice($game, 'Player:outoftime');
        } else {
            throw new LogicException($this->logger->formatPlayer($player, 'Player:outoftime'));
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
        if($winner = $game->getWinner()) {
            $win = $winner->isWhite() ? -1 : 1;
        } else {
            $win = 0;
        }
        list($whiteElo, $blackElo) = $this->calculator->calculate($white->getElo(), $black->getElo(), $win);
        $white->setEloDiff($whiteEloDiff = $whiteElo - $white->getElo());
        $whiteUser->setElo($whiteElo);
        $black->setEloDiff($blackElo - $black->getElo());
        $blackUser->setElo($blackElo);

        $this->logger->notice($game, sprintf('Elo exchanged: %s', $whiteEloDiff));
    }
}
