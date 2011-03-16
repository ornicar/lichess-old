<?php

namespace Bundle\LichessBundle\Elo;

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
     * kFactor
     * @see http://en.wikipedia.org/wiki/Elo_rating_system#Most_accurate_K-factor
     *
     * @var float
     */
    protected $kFactor;

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
     * Instanciates an ELO calculator
     *
     * @param float $kFactor
     */
    public function __construct($kFactor)
    {
        $this->kFactor = $kFactor;
    }

    /**
     * Calculate both players new Elos
     *
     * @param float $playerOneElo
     * @param float $playerTwopponentElo
     * @param int   $win Game result (-1: p1 win, 0: draw, +1: p2 win)
     * @return array playerOneNewElo, playerTwoNewElo
     */
    public function calculate($playerOneElo, $playerTwopponentElo, $win)
    {
        $playerOneNewElo = $this->calculatePlayerElo($playerOneElo, $playerTwopponentElo, -$win);
        $playerTwoNewElo = $this->calculatePlayerElo($playerTwopponentElo, $playerOneElo, $win);

        return array($playerOneNewElo, $playerTwoNewElo);
    }

    /**
     * Calculate a single player new elo
     *
     * @param int $playerElo
     * @param int $opponentElo
     * @param int $win
     * @return int
     */
    protected function calculatePlayerElo($playerElo, $opponentElo, $win)
    {
        $score      = $this->calculateScore($win);
        $expected   = $this->calculateExpected($playerElo, $opponentElo);
        $difference = $this->kFactor * ($score - $expected);

        return round($playerElo + $difference);
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
