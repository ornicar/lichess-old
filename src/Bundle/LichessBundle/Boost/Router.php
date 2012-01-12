<?php

/**
 * The purpose of this flat script is to dramatically improve performances, and save my webserver.
 * It handles the synchronization requests (95% of the traffic)
 * If APC cache exists for the user, and no event has occured since previous synchronization (90% of the requests)
 * The application is not run and this script can deliver a response in less than 0.1 milliseconds.
 * If this script returns, the normal Symfony application is run.
 **/

$url = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];

// sends an http response
function _lichess_boost_send_response($content, $type)
{
  $content = (string)$content;
  header('HTTP/1.0 200 OK');
  header('content-type: '.$type);
  header('content-length: '.strlen($content)); // short content length prevents gzip

  exit((string)$content);
}

// Redirect urls containing the frontend controller
if (0 === strncmp($url, '/index.php', 10)) {
  $newUrl = substr($url, 10);
  if (empty($newUrl)) $newUrl = '/';
  header("Location: ".$newUrl, true , 301);
  exit;
}

// Handle user ping
if (0 === strncmp($url, '/ping', 5)) {
  require_once(__DIR__.'/Handler.php');
  _lichess_boost_send_response(\Bundle\LichessBundle\Boost\Handler::ping(), 'application/json');
}
// Handle number of active players requests
elseif(0 === strncmp($url, '/how-many-players-now', 21)) {
  require_once(__DIR__.'/Handler.php');
  _lichess_boost_send_response(\Bundle\LichessBundle\Boost\Handler::howManyPlayersNow(), 'text/plain');
}
