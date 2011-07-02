<?php

namespace Lichess\ChartBundle\Chart;

use Bundle\LichessBundle\Document\GameRepository;
use Bundle\LichessBundle\Document\Game;

class GameEndChart
{
    /**
     * User repository
     *
     * @var GameRepository
     */
    protected $gameRepository;

    public function __construct(GameRepository $gameRepository)
    {
        $this->gameRepository = $gameRepository;
    }

    public function getColumns()
    {
        return array(
            array('string', 'End'),
            array('number', 'Number of games')
        );
    }

    public function getRows()
    {
        $ends = array(
            'mates' => $this->getNbGameByStatus(Game::MATE),
            'resigns' => $this->getNbGameByStatus(Game::RESIGN),
            'stalemates' => $this->getNbGameByStatus(Game::STALEMATE),
            'timeouts' => $this->getNbGameByStatus(Game::TIMEOUT),
            'disconnects' => $this->getNbGameByStatus(Game::OUTOFTIME),
            'draws' => $this->getNbGameByStatus(Game::DRAW),
        );

        $data = array();
        foreach ($ends as $text => $nb) {
            $data[] = array(number_format($nb).' '.$text, $nb);
        }

        return $data;
    }

    public function getNbGameByStatus($status)
    {
        return $this->gameRepository->countByStatus($status);
    }
}
