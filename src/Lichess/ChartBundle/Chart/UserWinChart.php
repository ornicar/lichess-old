<?php

namespace Lichess\ChartBundle\Chart;

use Bundle\LichessBundle\Critic\UserCritic;

class UserWinChart
{
    /**
     * User critic
     *
     * @var Critic
     */
    protected $critic;

    public function __construct(UserCritic $critic)
    {
        $this->critic = $critic;
    }

    public function getColumns()
    {
        return array(
            array('string', 'Result'),
            array('number', 'Games')
        );
    }

    public function getRows()
    {
        return array (
            array($this->critic->getNbWins().' Wins', $this->critic->getNbWins()),
            array($this->critic->getNbLosses().' Losses', $this->critic->getNbLosses()),
            array($this->critic->getNbDraws().' Draws', $this->critic->getNbDraws())
        );
    }
}
