<?php

namespace Bundle\LichessBundle\Benchmark;

class SyncBenchmark extends Benchmark
{
    public function testPlaySpeed()
    {
        $player = $this->createPlayer();
        $nbMoves = count($this->getMoves());
        $iterations = 1;
        $start = microtime(true);
        $hashes = array($player->getFullHash(), $player->getOpponent()->getFullHash());
        foreach($this->getMoves() as $it => $move) {
            list($from, $to) = explode(' ', $move);
            $player = $players[$it%2];
            $this->client->request('POST', '/move/'.$player->getFullHash().'/'.$stackVersion, array('from' => $from, 'to' => $to));
        }
        $time = 1000 * (microtime(true) - $start);
        printf('%d games played in %01.2f ms'."\n", $iterations, $time);
        printf('%01.2f ms per game'."\n", $time/$iterations);
        printf('%01.2f ms per move'."\n", $time/$iterations/$nbMoves);
        die;
    }

    protected function getMoves()
    {
        return array( 'e2 e4', 'd7 d5', 'e4 d5', 'd8 d5', 'b1 c3', 'd5 a5', 'd2 d4', 'c7 c6', 'g1 f3', 'c8 g4', 'c1 f4', 'e7 e6', 'h2 h3', 'g4 f3', 'd1 f3', 'f8 b4', 'f1 e2', 'b8 d7', 'a2 a3', 'e8 c8', 'a3 b4', 'a5 a1', 'e1 d2', 'a1 h1', 'f3 c6', 'b7 c6', 'e2 a6');
    }
}
