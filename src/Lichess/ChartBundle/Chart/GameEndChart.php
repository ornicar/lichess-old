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
            'mates' => $this->gameRepository->countByStatus(Game::MATE),
            'resigns' => $this->gameRepository->countByStatus(Game::RESIGN),
            'stalemates' => $this->gameRepository->countByStatus(Game::STALEMATE),
            'timeouts' => $this->gameRepository->countByStatus(Game::TIMEOUT),
            'disconnects' => $this->gameRepository->countByStatus(Game::OUTOFTIME),
            'draws' => $this->gameRepository->countByStatus(Game::DRAW),
        );

        $data = array();
        foreach ($ends as $text => $nb) {
            $data[] = array($nb.' '.$text, $nb);
        }

        return $data;
    }
}
