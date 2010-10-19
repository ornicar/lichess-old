<?php

use Bundle\LichessBundle\Persistence\FilePersistence;
use Bundle\LichessBundle\Persistence\MongoDBPersistence;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Entities\Game;

require_once(__DIR__.'/bootstrap.php');

require_once(__DIR__.'/../Persistence/MongoDBPersistence.php');

$totalTime = 0;
$dir = sys_get_temp_dir().'/lichess';
if(!is_dir($dir)) {
    mkdir($dir);
}
$driver = function()
{
    return new MongoDBPersistence();
};

$generator = new Generator();
$game = $generator->createGame();
$driver()->save($game);

print "Empty game\n";
printf('Size: %d B'."\n", $driver()->getGameSize($game));
lichess_test_performance_file_persistence($game, $driver);

$game = $generator->createGame();
$driver()->save($game);
$game->setStatus(Game::STARTED);
for($it=0; $it<300; $it++) {
    $game->getRoom()->addMessage($it%2 ? 'white' : 'black', str_repeat('blah blah ', rand(1, 10)));
}
$driver();

print "Real world game\n";
printf('Size: %d B'."\n", $driver()->getGameSize($game));
lichess_test_performance_file_persistence($game, $driver);

printf('Total: %01.2f ms'."\n", $totalTime);

function lichess_test_performance_file_persistence(Game $game, $driver)
{
    global $totalTime;
    $hash = $game->getHash();
    $iterations = 1000;

    $start = microtime(true);
    for($it=0; $it<$iterations; $it++) {
        $persistence = $driver();
        $persistence->find($hash);
        unset($persistence);
    }
    $totalTime += $time = 1000 * (microtime(true) - $start);

    printf('%s %d games in %01.2f ms'."\n", 'Find', $iterations, $time);
    printf('%01.2f ms per find'."\n", $time/$iterations);

    $start = microtime(true);
    for($it=0; $it<$iterations; $it++) {
        $persistence = $driver();
        $persistence->save($game);
        unset($persistence);
    }
    $totalTime += $time = 1000 * (microtime(true) - $start);

    printf('%s %d games in %01.2f ms'."\n", 'Save', $iterations, $time);
    printf('%01.2f ms per save'."\n", $time/$iterations);
}
