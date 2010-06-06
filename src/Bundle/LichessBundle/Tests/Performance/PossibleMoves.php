<?php

use Bundle\LichessBundle\Persistence\FilePersistence;
use Bundle\LichessBundle\Chess\Generator;

require_once(__DIR__.'/../gameBootstrap.php');

require_once(__DIR__.'/../../Chess/Manipulator.php');

$generator = new Generator();
$game = $generator->createGame();
$player = $game->getPlayer('white');

$iterations = 10;

$start = microtime(true);
for($it=0; $it<$iterations; $it++) {
    $player->getPossibleMoves();
}
$time = 1000 * (microtime(true) - $start);
printf('%d possible moves requests in %01.2f ms'."\n", $iterations, $time);
printf('%01.2f ms per request'."\n", $time/$iterations);

passthru('phpunit '.__DIR__.'/../AllTests.php');
