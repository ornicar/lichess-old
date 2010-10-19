<?php

require_once __DIR__.'/../../../../lichess/LichessKernel.php';

$kernel = new LichessKernel('prod', false);
$kernel->boot();
$container = $kernel->getContainer();
$moves = array( 'e2 e4', 'd7 d5', 'e4 d5', 'd8 d5', 'b1 c3', 'd5 a5', 'd2 d4', 'c7 c6', 'g1 f3', 'c8 g4', 'c1 f4', 'e7 e6', 'h2 h3', 'g4 f3', 'd1 f3', 'f8 b4', 'f1 e2', 'b8 d7', 'a2 a3', 'e8 c8', 'a3 b4', 'a5 a1', 'e1 d2', 'a1 h1', 'f3 c6', 'b7 c6', 'e2 a6');
$iterations = 2;
$nbMoves = count($moves);
$start = microtime(true);

for($it=0; $it<$iterations; $it++) {
    $game = $container->get('lichess_generator')->createGame();
    $manipulator = new Bundle\LichessBundle\Chess\Manipulator($game);
    foreach($moves as $i => $move) {
        $manipulator->play($move);
    }
}

$time = 1000 * (microtime(true) - $start);
printf('%d games of %s moves played in %01.2f ms'."\n", $iterations, $nbMoves, $time);
printf('%01.2f ms per game'."\n", $time/$iterations);
printf('%01.2f ms per move'."\n", $time/$iterations/$nbMoves);
