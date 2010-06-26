<?php

use Bundle\LichessBundle\Persistence\FilePersistence;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Entities\Game;

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
$persistence->save($game);

print "Empty game\n";
lichess_test_performance_file_persistence($game, $persistence);

$game = $generator->createGame();
$persistence->save($game);
$game->setStatus(Game::STARTED);
for($it=0; $it<300; $it++) {
    $game->getRoom()->addMessage($it%2 ? 'white' : 'black', str_repeat('blah blah ', rand(1, 10)));
}
$persistence->save($game);

print "Real world game\n";
lichess_test_performance_file_persistence($game, $persistence);

function lichess_test_performance_file_persistence(Game $game, FilePersistence $persistence)
{
    $hash = $game->getHash();
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

    $file = $persistence->getGameFile($game);
    printf('Size: %d B'."\n", filesize($file));
}
