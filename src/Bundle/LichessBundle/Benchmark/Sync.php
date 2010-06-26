<?php

namespace Bundle\LichessBundle\Benchmark;

class SyncBenchmark extends Benchmark
{
    public function testSyncSpeed()
    {
        $player = $this->createPlayer();
        $playerHash = $player->getFullHash();

        $iterations = 100;

        $start = microtime(true);
        for($it=0; $it<$iterations; $it++) {
            $this->client->request('POST', '/sync/'.$playerHash.'/0');
        }
        $time = 1000 * (microtime(true) - $start);
        printf('%d syncs in %01.2f ms'."\n", $iterations, $time);
        printf('%01.2f ms per sync'."\n", $time/$iterations);
        die;
    }
}
