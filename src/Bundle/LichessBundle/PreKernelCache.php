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
    require_once __DIR__.'/Sync/Memory.php';
    // params: lichess.synchronizer.soft_timeout, lichess.synchronizer.hard_timeout
    return new Bundle\LichessBundle\Sync\Memory(20, 120);
}

// sends an http response
function _lichess_send_response($content, $type)
{
    $content = (string)$content;
    header('HTTP/1.0 200 OK');
    header('content-type: '.$type);
    header('content-length: '.strlen($content)); // short content length prevents gzip
    exit((string)$content);
}

// Handle number of active players requests
if(0 === strpos($url, '/how-many-players-now')) {
    _lichess_send_response(_lichess_get_synchronizer()->getNbActivePlayers(), 'text/plain');
}
// Handle authenticated user ping
elseif (0 === strpos($url, '/ping/') && preg_match('#^/ping/(?P<username>\w+)(\?.+|$)#x', $url, $matches)) {
    $synchronizer = _lichess_get_synchronizer();
    $synchronizer->setUsernameOnline($matches['username']);
    $content = sprintf('{"nbp":%d,"nbm":%d}', $synchronizer->getNbActivePlayers(), apc_fetch('nbm.'.$matches['username']));
    _lichess_send_response($content, 'application/json');
}
