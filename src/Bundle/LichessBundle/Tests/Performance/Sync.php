<?php

use Bundle\LichessBundle\Persistence\FilePersistence;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Synchronizer;
use Bundle\LichessBundle\Entities\Game;

require_once(__DIR__.'/bootstrap.php');
require_once(__DIR__.'/../../Persistence/PersistenceInterface.php');
require_once(__DIR__.'/../../Persistence/FilePersistence.php');
require_once(__DIR__.'/../../Chess/Synchronizer.php');

$generator = new Generator();
$game = $generator->createGame();
$gameHash = $game->getHash();
$player = $game->getPlayer('white');
$game->setCreator($player);
$game->setStatus(Game::STARTED);
for($it=0; $it<50; $it++) {
    $game->getRoom()->addMessage($it%2 ? 'white' : 'black', str_repeat('blah blah ', rand(1, 10)));
}
$playerHash = $player->getFullHash();
$opponent = $player->getOpponent();
$opponentHash = $opponent->getFullHash();
$dir = sys_get_temp_dir();
$persistence = new FilePersistence($dir);
$persistence->save($game);
$synchronizer = new Synchronizer(5);

$iterations = 1000;

$start = microtime(true);
for($it=0; $it<$iterations; $it++) {
  $persistence->find($gameHash);
  $synchronizer->update($player);
  $synchronizer->isConnected($opponent);
  $persistence->save($game);
}
$time = 1000 * (microtime(true) - $start);
printf('%d syncs in %01.2f ms'."\n", $iterations, $time);
printf('%01.2f ms per sync'."\n", $time/$iterations);
