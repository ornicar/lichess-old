<?php

namespace Bundle\LichessBundle\Chess;

class Hardware
{
    protected $loadAverageTtl = 5;

    public function getLoadAverage()
    {
        $value = apc_fetch('lichess.load_average');
        if(false === $value) {
            $value = $this->doGetLoadAverage();
            apc_store('lichess.load_average', $value, $this->loadAverageTtl);
        }

        return $value;
    }

    protected function doGetLoadAverage()
    {
        if('\\' == DIRECTORY_SEPARATOR) {
            return '?';
        }
        $loadAverage = sys_getloadavg();
        $loadAverage = $loadAverage[1];
        $cpus = $this->getNbCpus();
        $loadAverage *= (100/$cpus);

        return $loadAverage;
    }

    protected function getNbCpus()
    {
        $value = apc_fetch('lichess.nb_cpus');
        if(false === $value) {
            exec('grep processor /proc/cpuinfo', $cpus);
            $value = count($cpus) ?: 1;
            apc_store('lichess.nb_cpus', $value);
        }

        return $value;
    }
}
