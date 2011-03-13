<?php

namespace Bundle\LichessBundle\Elo;

use Bundle\LichessBundle\Document\History;

class Chart
{
    public function getUrl(History $history, $size)
    {
        $elos = $history->getEloByTs();
        $elos = $this->reduce($elos);
        $min = 20*round((min($elos) - 10)/20);
        $max = 20*round((max($elos) + 10)/20);
        $dots = array_map(function($e) use($min, $max) { return round(($e - $min) / ($max - $min) * 100); }, $elos);
        $yStep = ($max - $min) / 4 ;
        return sprintf('%scht=lc&chs=%s&chd=t:%s&chxt=y&chxr=%s&chf=%s',
            'http://chart.apis.google.com/chart?',
            $size,
            implode(',', $dots),
            implode(',', array(0, $min, $max, $yStep)),
            'bg,s,65432100' // Transparency
        );
    }

    protected function reduce(array $elos)
    {
        $count = count($elos);
        $limit = 100;
        if ($count <= $limit) {
            return $elos;
        }

        $ts = array_keys($elos);
        $es = array_values($elos);
        $reduced = array();
        $factor = $count/$limit;
        for ($i = 0; $i < $limit; $i++) {
            $key = round($i*$factor);
            $reduced[$ts[$key]] = $es[$key];
        }

        return $reduced;
    }
}
