<?php

namespace Lichess\ChartBundle\Chart;

use Bundle\LichessBundle\Critic\UserCritic;
use Symfony\Component\Translation\TranslatorInterface;

class UserWinChart
{
    /**
     * User critic
     *
     * @var Critic
     */
    protected $critic;

    protected $translator;

    public function __construct(UserCritic $critic, TranslatorInterface $translator)
    {
        $this->critic = $critic;
        $this->translator = $translator;
    }

    public function hasData()
    {
        return $this->critic->getNbGames() > 0;
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
            array($this->translator->trans('%nb% wins', array('%nb%' => $this->critic->getNbWins())), $this->critic->getNbWins()),
            array($this->translator->trans('%nb% losses', array('%nb%' => $this->critic->getNbLosses())), $this->critic->getNbLosses()),
            array($this->translator->trans('%nb% draws', array('%nb%' => $this->critic->getNbDraws())), $this->critic->getNbDraws())
        );
    }
}
