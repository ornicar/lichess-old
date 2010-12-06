<?php

/**
 * The purpose of this flat script is to dramatically improve performances, and save my webserver.
 * It handles the synchronization requests (95% of the traffic)
 * If cache exists for the user, and no event has occured since previous synchronization (90% of the requests)
 * The application is not run and this script can deliver a response in less than 0.1 milliseconds.
 * If this script returns, the normal Symfony application is run.
 **/

// Configuration
$timeout = 20;

// Get url
$url = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];

use Bundle\LichessBundle\Storage;
require_once __DIR__ . '/Storage/StorageInterface.php';
if (function_exists('apc_store') && ini_get('apc.enabled')) {
    require_once __DIR__ . '/Storage/Apc.php';
    $storage = new Storage\Apc();
} elseif (function_exists('wincache_ucache_set') && ini_get('wincache.ucenabled')) {
    require_once __DIR__ . '/Storage/WinCache.php';
    $storage = new Storage\WinCache();
} else {
    // no storage is available
    return;
}


// Handle number of connected players requests
if('/how-many-players-now' === $url) {

    $nb = $storage->get('lichess.nb_players');
    if(false === $nb) {
        $it = $storage->getIterator('/alive$/');
        $nb = 0;
        $limit = time() - $timeout;
        foreach($it as $i) {
            $storage->get($i['key']); // clear invalidated entries
            if($i['mtime'] >= $limit) ++$nb;
        }
        $storage->store('lichess.nb_players', $nb, 2);
    }

    // Return minimalist JSON response telling the number of connected players
    header('HTTP/1.0 200 OK');
    header('content-type: text/plain');
    die((string)$nb);
}

// Handle game synchronization

if (0 === strpos($url, '/sync/') && preg_match('#^/sync/(?P<id>[\w-]{8})/(?P<color>(white|black))/(?P<version>\d+)/(?P<playerFullId>([\w-]{12}|))$#x', $url, $matches)) {
    $id = $matches['id'];
    $color = $matches['color'];
    $clientVersion = $matches['version'];
    $playerFullId = $matches['playerFullId'];
    $opponentColor = 'white' === $color ? 'black' : 'white';
}
else return;

// Get user cache from Storage
$userVersion = $storage->get($id.'.'.$color.'.data');

// If the user has no cache, hit the application
if(false === $userVersion) return;

// If the client and server version differ, update the client
if($userVersion != $clientVersion) return;

if($playerFullId) {
    // Set the client as connected
    $storage->store($id.'.'.$color.'.alive', 1, $timeout);
}

// Check is opponent is connected
if($playerFullId) {
    $isOpponentAlive = $storage->get($id.'.'.$opponentColor.'.alive') ? 1 : 0;
}
else {
    $isOpponentAlive = true;
}

// Return minimalist JSON response telling the state of the opponent
header('HTTP/1.0 200 OK');
header('content-type: application/json');
die('{"o": '.$isOpponentAlive.'}');
