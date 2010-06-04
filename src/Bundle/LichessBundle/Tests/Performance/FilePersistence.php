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

$iterations = 1000;

$start = microtime(true);
for($it=0; $it<$iterations; $it++) {
  $persistence->find($hash);
}
$time = 1000 * (microtime(true) - $start);

printf('%s %d games in %01.2f ms'."\n", 'Find', $iterations, $time);
printf('%01.2f ms per find'."\n", $time/$iterations);

$start = microtime(true);
for($it=0; $it<$iterations; $it++) {
  $persistence->save($game);
}
$time = 1000 * (microtime(true) - $start);

printf('%s %d games in %01.2f ms'."\n", 'Save', $iterations, $time);
printf('%01.2f ms per save'."\n", $time/$iterations);

printf('Size: %d B'."\n", filesize($file));
