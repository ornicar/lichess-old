<?php

$path = '/usr/bin/';
//$path = '/usr/games/';

$command = '{path}fortune';

if (isset($_GET['cowsay'])) {
    $command .= ' | {path}cowsay';
}

$command = str_replace('{path}', $path, $command);
$response = shell_exec($command);

if (isset($_GET['callback'])) {
    $response = str_replace(array("\\", "\n", '"'), array("\\\\", "\\n", '\\"'), $response);
    $response = sprintf('%s("%s")', $_GET['callback'], $response);
    header("Content-type: application/javascript");
} else {
    header("Content-type: text/plain");
}

print $response;
