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
            array('number', sprintf('White time - %s', $this->players['white']->getUsername())),
            array('number', sprintf('Black time - %s', $this->players['black']->getUsername()))
        );
    }

    public function getRows()
    {
        $blackTimes = $this->players['black']->getMoveTimes();
        $data = array();
        foreach ($this->players['white']->getMoveTimes() as $move => $whiteTime) {
            if (isset($blackTimes[$move])) {
                $data[] = array(
                    (string) $move,
                    $whiteTime,
                    $blackTimes[$move]
                );
            }
        }

        return $data;
    }
}
