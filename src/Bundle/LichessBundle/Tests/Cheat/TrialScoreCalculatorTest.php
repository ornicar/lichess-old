<?php

namespace Bundle\LichessBundle\Cheat;

class TrialScoreCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider trialScoreProvider
     */
    public function testCalculateScore($blurFactor, $timePerMove, $expected)
    {
        $calculator = new TrialScoreCalculator();

        $this->assertEquals($expected, round($calculator->calculateScore($blurFactor, $timePerMove)));
    }

    public function trialScoreProvider()
    {
        return array(
            array(50, 3, 62),
            array(50, 10, 53),
            array(50, 60, 33),
            array(80, 3, 99),
            array(80, 10, 84),
            array(80, 60, 52),
            array(100, 3, 100),
            array(100, 10, 100),
            array(100, 60, 65)
        );
    }

    /**
     * @dataProvider probabilityToBlurProvider
     */
    public function testProbabilityToBlur($timePerMove, $expected)
    {
        $calculator = new TrialScoreCalculator();

        $this->assertEquals($expected, $calculator->calculateProbabilityToBlur($timePerMove));
    }

    public function probabilityToBlurProvider()
    {
        return array(
            array(0, 0.01),
            array(3, 0.01),
            array(5, 0.01),
            array(7, 0.1),
            array(10, 0.2),
            array(20, 0.3),
            array(30, 0.4),
            array(40, 0.5),
            array(60, 0.6)
        );
    }
}
