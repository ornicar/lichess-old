<?php

/**
 * The purpose of this flat script is to dramatically improve performances, and save my webserver.
 * It handles the synchronization requests (95% of the traffic)
 * If APC cache exists for the user, and no event has occured since previous synchronization (90% of the requests)
 * The application is not run and this script can deliver a response in less than 0.1 milliseconds.
 * If this script returns, the normal Symfony application is run.
 **/

$url = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];
//
// param: lichess.memory.soft_timeout
$softTimeout = 7;

function _lichess_get_synchronizer($softTimeout)
{
    require_once __DIR__.'/Sync/Memory.php';
    return new Bundle\LichessBundle\Sync\Memory($softTimeout, 120);
}
function _lichess_get_hook_synchronizer($softTimeout)
{
    require_once __DIR__.'/../../Lichess/OpeningBundle/Sync/Memory.php';
    return new Lichess\OpeningBundle\Sync\Memory($softTimeout);
}
function _lichess_get_user_synchronizer($softTimeout)
{
    require_once __DIR__.'/../../Application/UserBundle/Online/Cache.php';
    return new Application\UserBundle\Online\Cache($softTimeout);
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

// Handle user ping
if (0 === strpos($url, '/ping')) {
    $synchronizer = _lichess_get_synchronizer($softTimeout);
    $data = array('nbp' => $synchronizer->getNbActivePlayers());
    if (isset($_GET['player_key'])) {
        $synchronizer->setPlayerKeyAlive($_GET['player_key']);
    }
    if (isset($_GET['watcher'])) {
        $synchronizer->registerWatcher($_GET['watcher']);
    }
    if (isset($_GET['get_nb_watchers'])) {
        $data['nbw'] = $synchronizer->getNbWatchers($_GET['get_nb_watchers']);
    }
    if (isset($_GET['hook_id'])) {
        _lichess_get_hook_synchronizer($softTimeout)->setHookIdAlive($_GET['hook_id']);
    }
    if (isset($_GET['username'])) {
        _lichess_get_user_synchronizer($softTimeout)->setUsernameOnline($_GET['username']);
        $data['nbm'] = (int) apc_fetch('nbm.'.$_GET['username']);
    }
    _lichess_send_response(json_encode($data), 'application/json');
}
// Handle number of active players requests
elseif(0 === strpos($url, '/how-many-players-now')) {
    _lichess_send_response(_lichess_get_synchronizer($softTimeout)->getNbActivePlayers(), 'text/plain');
}
