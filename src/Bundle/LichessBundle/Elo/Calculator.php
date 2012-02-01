<?php

namespace Bundle\LichessBundle\Elo;

use Application\UserBundle\Document\User;

/**
 * Calculates players ELO
 * @see http://en.wikipedia.org/wiki/Elo_rating_system
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @license MIT {@link http://opensource.org/licenses/mit-license.html}
 */
class Calculator
{
    /**
     * Player 1 wins
     */
    const P1WIN = -1;

    /**
     * No player wins
     */
    const DRAW  = 0;

    /**
     * Player 2 wins
     */
    const P2WIN = 1;

    /**
     * Calculate both players new Elos
     *
     * @param int   $win Game result (-1: p1 win, 0: draw, +1: p2 win) see class constants
     * @return array playerOneNewElo, playerTwoNewElo
     */
    public function calculate(User $user1, User $user2, $win)
    {
        $user1NewElo = $this->calculateUserElo($user1, $user2->getElo(), -$win);
        $user2NewElo = $this->calculateUserElo($user2, $user1->getElo(), $win);

        return array($user1NewElo, $user2NewElo);
    }

    /**
     * Only returns the elo diff
     */
    public function calculateDiff(User $user1, User $user2, $win)
    {
        $user1NewElo = $this->calculateUserElo($user1, $user2->getElo(), -$win);

        return $user1NewElo - $user1->getElo();
    }

    /**
     * Calculate a single player new elo
     *
     * @param User $user
     * @param int $opponentElo
     * @param int $win
     * @return int
     */
    protected function calculateUserElo(User $user, $opponentElo, $win)
    {
        $score      = $this->calculateScore($win);
        $expected   = $this->calculateExpected($user->getElo(), $opponentElo);
        $kFactor    = $this->nbRatedGamesToKfactor($user->getNbRatedGames());
        $difference = 2 * $kFactor * ($score - $expected);

        return round($user->getElo() + $difference);
    }

    /**
     * kFactor
     * @see http://en.wikipedia.org/wiki/Elo_rating_system#Most_accurate_K-factor
     *
     * @var float
     */
    public function nbRatedGamesToKfactor($nb)
    {
        return round(
            $nb > 20
            ? 16
            : 50 - $nb * 34 / 20
        );
    }

    /**
     * Calculate base score:
     * win: 1
     * draw: 0.5
     * loss: 0
     *
     * @param int $win
     * @return float
     */
    protected function calculateScore($win)
    {
        return (1+$win)/2;
    }

    /**
     * Calculate win expectation
     *
     * @param int $playerElo
     * @param int $opponentElo
     * @return float
     */
    protected function calculateExpected($playerElo, $opponentElo)
    {
        return 1/(1+pow(10, (($opponentElo-$playerElo)/400)));
    }
}
