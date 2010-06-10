<?php

ini_set('xdebug.profiler_enable', 1);
ini_set('xdebug.profiler_output_dir', '/tmp');
ini_set('xdebug.profiler_output_name', 'cachegrind.out.44444');
use Bundle\LichessBundle\Persistence\FilePersistence;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;

require_once(__DIR__.'/../gameBootstrap.php');

require_once(__DIR__.'/../../Chess/Manipulator.php');

$generator = new Generator();
$game = $generator->createGame();
$player = $game->getPlayer('white');

$iterations = 1;
$nbMoves = 27;

$start = microtime(true);
for($it=0; $it<$iterations; $it++) {
    _playWholeGame();
}
$time = 1000 * (microtime(true) - $start);
printf('%d games played in %01.2f ms'."\n", $iterations, $time);
printf('%01.2f ms per game'."\n", $time/$iterations);
printf('%01.2f ms per move'."\n", $time/$iterations/$nbMoves);

//passthru('phpunit '.__DIR__.'/../AllTests.php');

function _playWholeGame()
{
    $moves = array(
        'e2 e4',
        'd7 d5',
        'e4 d5',
        'd8 d5',
        'b1 c3',
        'd5 a5',
        'd2 d4',
        'c7 c6',
        'g1 f3',
        'c8 g4',
        'c1 f4',
        'e7 e6',
        'h2 h3',
        'g4 f3',
        'd1 f3',
        'f8 b4',
        'f1 e2',
        'b8 d7',
        'a2 a3',
        'e8 c8',
        'a3 b4',
        'a5 a1',
        'e1 d2',
        'a1 h1',
        'f3 c6',
        'b7 c6',
        'e2 a6'
    );

    $generator = new Generator();
    $game = $generator->createGame();
    $manipulator = new Manipulator($game->getBoard());
    foreach($moves as $move) {
        $manipulator->play($move);
    }
}
