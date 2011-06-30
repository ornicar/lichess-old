<?php

namespace Lichess\ChartBundle\Chart;

use Bundle\LichessBundle\Document\Player;

class PlayerMoveTimeChart
{
    protected $players;

    public function __construct(array $players)
    {
        $this->players = $players;
    }

    public function getColumns()
    {
        return array(
            array('string', 'Move'),
            array('number', 'White time'),
            array('number', 'Black time')
        );
    }

    public function getRows()
    {
        $blackTimes = $this->players['black']->getMoveTimes();
        $data = array();
        foreach ($this->players['white']->getMoveTimes() as $move => $whiteTime) {
            $data[] = array(
                $move,
                $whiteTime,
                $blackTimes[$move]
            );
        }

        return $data;
    }
}
