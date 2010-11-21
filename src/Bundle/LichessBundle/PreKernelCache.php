<?php

/**
 * The purpose of this flat script is to dramatically improve performances, and save my webserver.
 * It handles the synchronization requests (95% of the traffic)
 * If APC cache exists for the user, and no event has occured since previous synchronization (90% of the requests)
 * The application is not run and this script can deliver a response in less than 0.1 milliseconds.
 * If this script returns, the normal Symfony application is run.
 **/

// Configuration
$timeout = 20;

// Get url
$url = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];

// Handle number of connected players requests

if('/how-many-players-now' === $url) {
    $nb = apc_fetch('lichess.nb_players');
    if(false === $nb) {
        $it = new \APCIterator('user', '/alive$/', APC_ITER_MTIME | APC_ITER_KEY, 100, APC_LIST_ACTIVE);
        $nb = 0;
        $limit = time() - $timeout;
        foreach($it as $i) {
            apc_fetch($i['key']); // clear invalidated entries
            if($i['mtime'] >= $limit) ++$nb;
        }
        apc_store('lichess.nb_players', $nb, 2);
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

// Get user cache from APC
$userVersion = apc_fetch($id.'.'.$color.'.data');

// If the user has no cache, hit the application
if(false === $userVersion) return;

// If the client and server version differ, update the client
if($userVersion != $clientVersion) return;

if($playerFullId) {
    // Set the client as connected
    apc_store($id.'.'.$color.'.alive', 1, $timeout);
}

// Check is opponent is connected
if($playerFullId) {
    $isOpponentAlive = apc_fetch($id.'.'.$opponentColor.'.alive') ? 1 : 0;
}
else {
    $isOpponentAlive = true;
}

// Return minimalist JSON response telling the state of the opponent
header('HTTP/1.0 200 OK');
header('content-type: application/json');
die('{"o": '.$isOpponentAlive.'}');
