<?php

$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];

if(!preg_match('#^/sync/([\w\d]{10})/(\d+)$#', $path, $matches)) {
    return;
}

list($hash, $clientVersion) = array($matches[1], $matches[2]);

$cache = apc_fetch($hash.'.data');

if(!$cache) {
    return;
}

list($cacheVersion, $opponentHash) = explode('|', $cache);

if($cacheVersion != $clientVersion) {
    return;
}

apc_store($hash.'.alive', 1, 10);

$isOpponentAlive = apc_fetch($opponentHash.'.alive') ? 1 : 0;

header('HTTP/1.0 200 OK');
header('content-type: application/json');
die('{"o": '.$isOpponentAlive.'}');
