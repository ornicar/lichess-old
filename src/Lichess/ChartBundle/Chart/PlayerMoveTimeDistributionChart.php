<?php

namespace Lichess\ChartBundle\Chart;

use Bundle\LichessBundle\Document\Player;

class PlayerMoveTimeDistributionChart
{
    protected $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function getRows()
    {
        $moveTimes = $this->player->getMoveTimes();

        $data = array_count_values($moveTimes);

        $data = $this->reduceData($data);

        $data = $this->formatData($data);

        return $data;
    }

    public function getColumns()
    {
        return array(
            array('string', 'Time in seconds'),
            array('number', 'Number of moves')
        );
    }

    protected function reduceData(array $data)
    {
        ksort($data);
        $reduced = array();
        $get = function(array $array, $key) { return isset($array[$key]) ? $array[$key] : 0; };
        for ($i=0; $i<15; $i+=2) {
            if ($get($data, $i) || $get($data, $i+1)) {
                $reduced[sprintf('%d-%d', $i, $i+1)] = $get($data, $i) + $get($data, $i+1);
                unset($data[$i], $data[$i+1]);
            }
        }
        if (!empty($data)) {
            $reduced['17+'] = array_sum($data);
        }

        return $reduced;
    }

    protected function formatData(array $data)
    {
        $formatted = array();
        foreach ($data as $time => $number) {
            $formatted[] = array(sprintf('%ss. (%d moves)', $time, $number), $number);
        }

        return $formatted;
    }
}
