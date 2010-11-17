<?php

namespace Bundle\LichessBundle\Elo;

class Calculator
{
    protected $kFactor;

    const P1WIN = -1;
    const DRAW  = 0;
    const P2WIN = 1;

    public function __construct($kFactor)
    {
        $this->kFactor = $kFactor;
    }

    /**
     * Calculate p1 and p2 new Elos
     *
     * @param float $p1Elo
     * @param float $p2Elo
     * @param int   $win Game result (-1: p1 win, 0: draw, +1: p2 win)
     * @return array newP1Elo, newP2Elo
     */
    public function calculate($p1Elo, $p2Elo, $win)
    {
        $newP1Elo = $this->calculatePlayerElo($p1Elo, $p2Elo, -$win);
        $newP2Elo = $this->calculatePlayerElo($p2Elo, $p1Elo, $win);

        return array(round($newP1Elo, 2), round($newP2Elo, 2));
    }

    protected function calculatePlayerElo($pElo, $oElo, $win)
    {
        $score    = $this->calculateScore($win);
        $expected = $this->calculateExpected($pElo, $oElo);

        return $pElo + $this->kFactor * ($score - $expected);
    }

    protected function calculateScore($win)
    {
        return (1+$win)/2;
    }

    protected function calculateExpected($pElo, $oElo)
    {
        return 1/(1+pow(10, (($oElo-$pElo)/400)));
    }
}
