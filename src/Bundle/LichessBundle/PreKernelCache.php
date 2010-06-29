<?php

/**
 * The purpose of this flat script is to dramatically improve performances, and save my webserver.
 * It handles the synchronization requests (95% of the traffic)
 * If APC cache exists for the user, and no event has occured since previous synchronization (90% of the requests)
 * The application is not run and this script can deliver a response in less than 0.1 milliseconds.
 * If this script returns, the normal Symfony application is run.
 **/

// Get url
$url = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];

// Handle only sync urls
if (0 === strpos($url, '/sync') && preg_match('#^/sync/(?P<hash>[\w]{10})/(?P<version>\d+)$#x', $url, $matches)) {
    $hash = $matches['hash'];
    $clientVersion = $matches['version'];
}
else {
    return;
}

// Get user cache from APC
$cache = apc_fetch($hash.'.data');

// If the user has no cache, hit the application
if(!$cache) return;

// Extract infos from cache data
list($cacheVersion, $opponentHash) = explode('|', $cache);

// If the client and server version differ, update the client
if($cacheVersion != $clientVersion) return;

// Set the client as connected
apc_store($hash.'.alive', 1, 10);

// Check is opponent is connected
$isOpponentAlive = apc_fetch($opponentHash.'.alive') ? 1 : 0;

// Return minimalist JSON response telling the state of the opponent
header('HTTP/1.0 200 OK');
header('content-type: application/json');
die('{"o": '.$isOpponentAlive.'}');
