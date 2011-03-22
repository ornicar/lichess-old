<?php

// Start timer
$start = microtime(true);
ob_start();

// Symfony2 boot
require_once __DIR__.'/../lichess/bootstrap.php';
require_once __DIR__.'/../lichess/LichessKernel.php';

use Symfony\Component\HttpFoundation\Request;

// Run application
$kernel = new LichessKernel('dev', true);
$kernel->handle(Request::createFromGlobals())->send();

// Display timer
print str_replace('[[time]]', round(1000*(microtime(true) - $start)).'ms', ob_get_clean());
