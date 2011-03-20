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

// instanciate a synchronizer
function _lichess_get_synchronizer()
{
    require_once __DIR__.'/Chess/LowLevelSynchronizer.php';
    // params: lichess.synchronizer.soft_timeout, lichess.synchronizer.hard_timeout
    return new Bundle\LichessBundle\Chess\LowLevelSynchronizer(10, 120);
}

// Send response to the client
function _lichess_return_response($text, $type = 'application/json')
{
    header('HTTP/1.0 200 OK');
    header('content-type: '.$type);
    die((string)$text);
}

// Handle number of active players requests

if(0 === strpos($url, '/how-many-players-now')) {
    _lichess_return_response(_lichess_get_synchronizer()->getNbActivePlayers(), 'text/plain');
}

// Handle authenticated user ping

if (0 === strpos($url, '/ping/') && preg_match('#^/ping/(?P<username>\w+)$#x', $url, $matches)) {
    $username = $matches['username'];
    $synchronizer = _lichess_get_synchronizer();
    $synchronizer->setUsernameOnline($username);
    _lichess_return_response(sprintf('{"nbp":%d,"nbm":%d}', $synchronizer->getNbActivePlayers(), apc_fetch('nbm.'.$username)));
}

// Handle game synchronization
if (0 === strpos($url, '/sync/') && preg_match('#^/sync/(?P<id>[\w-]{8})/(?P<color>(white|black))/(?P<version>\d+)/(?P<playerFullId>([\w-]{12}|))$#x', $url, $matches)) {
    $id            = $matches['id'];
    $color         = $matches['color'];
    $clientVersion = $matches['version'];
    $playerFullId  = $matches['playerFullId'];
    $opponentColor = 'white' === $color ? 'black' : 'white';
} else {
    return;
}

$synchronizer = _lichess_get_synchronizer();

// Get user cache from APC
$userVersion = $synchronizer->getVersion($id, $color);

// If the user has no cache, hit the application
if(false === $userVersion) return;

// If the client and server version differ, update the client
if($userVersion != $clientVersion) return;

if($playerFullId) {
    // Set the client as active
    $synchronizer->setAlive($id, $color);
}

// Check is opponent is active
if($playerFullId) {
    $opponentActivity = $synchronizer->getActivity($id, $opponentColor);
} else {
    $opponentActivity = "2";
}

_lichess_return_response('{"oa":'.$opponentActivity.'}');
