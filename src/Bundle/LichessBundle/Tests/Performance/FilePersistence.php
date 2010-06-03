<?php

use Bundle\LichessBundle\Persistence\FilePersistence;
use Bundle\LichessBundle\Chess\Generator;

require_once(__DIR__.'/bootstrap.php');

require_once(__DIR__.'/../../Persistence/PersistenceInterface.php');
require_once(__DIR__.'/../../Persistence/FilePersistence.php');

$dir = sys_get_temp_dir().'/lichess';
if(!is_dir($dir)) {
    mkdir($dir);
}
$persistence = new FilePersistence($dir);
$generator = new Generator();
$game = $generator->createGame();
$hash = $game->getHash();
$file = $persistence->getGameFile($game);

$persistence->save($game);

$iterations = 2000;

$start = microtime(true);
for($it=0; $it<$iterations; $it++) {
  $persistence->find($hash);
}
$time = microtime(true) - $start;

printf('%s %d games in %01.2f ms'."\n", 'Find', $iterations, $time);

$start = microtime(true);
for($it=0; $it<$iterations; $it++) {
  $persistence->save($game);
}
$time = microtime(true) - $start;

printf('%s %d games in %01.2f ms'."\n", 'Save', $iterations, $time);

printf('Size: %d B'."\n", filesize($file));
