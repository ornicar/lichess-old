<?php

namespace Bundle\LichessBundle\Cheat;

/**
 * Calculates a trial score
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class TrialScoreCalculator
{
    /**
     * Return the score between 0 and 100
     *
     * @return float
     **/
    public function calculateScore($blurFactor, $timePerMove)
    {
        $blurFactor        = min(100, max(1, $blurFactor));
        $timePerMove       = min(60, max(1, $timePerMove));
        $probabilityToBlur = $this->calculateProbabilityToBlur($timePerMove);

        $score = $blurFactor * (1.25 - $probabilityToBlur);

        $score = round(min(100, max(1, $score)), 1);

        return $score;
    }

    public function calculateProbabilityToBlur($timePerMove)
    {
        switch (true) {
            case $timePerMove <= 5: return 0.01;
            case $timePerMove <= 8: return 0.1;
            case $timePerMove <= 10: return 0.2;
            case $timePerMove <= 20: return 0.3;
            case $timePerMove <= 30: return 0.4;
            case $timePerMove <= 40: return 0.5;
            default: return 0.6;
        }
    }
}
