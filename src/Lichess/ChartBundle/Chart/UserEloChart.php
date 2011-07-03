<?php

namespace Lichess\ChartBundle\Chart;

use Bundle\LichessBundle\Document\History;

class UserEloChart
{
    /**
     * Elo history
     *
     * @var History
     */
    protected $history;

    /**
     * Maximum number of data points
     *
     * @var int
     */
    protected $points = 100;

    public function __construct(History $history)
    {
        $this->history = $history;
    }

    public function hasData()
    {
        return $this->history->size() > 1;
    }

    public function getColumns()
    {
        return array(
            array('string', 'Game'),
            array('number', 'Elo')
        );
    }

    public function getRows()
    {
        $elos = $this->history->getEloByTs();
        $elos = $this->reduce($elos);

        $data = array();
        foreach ($elos as $ts => $elo) {
            $date = date('M j', $ts);
            $data[] = array($date, $elo);
        }

        return $data;
    }

    protected function reduce(array $elos)
    {
        $count = count($elos);
        if ($count <= $this->points) {
            return $elos;
        }

        $ts = array_keys($elos);
        $es = array_values($elos);
        $reduced = array();
        $factor = $count/$this->points;
        for ($i = 0; $i < $this->points; $i++) {
            $key = round($i*$factor);
            $reduced[$ts[$key]] = $es[$key];
        }
        // prevents chart lag: add last data point to the end
        if (end($reduced) != end($es)) {
            $reduced[end($ts)] = end($es);
        }

        return $reduced;
    }
}
