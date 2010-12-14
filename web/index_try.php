<?php

// Try PreKernelCache
require_once __DIR__.'/../src/Bundle/LichessBundle/PreKernelCache.php';

// Start timer
$start = microtime(true);
ob_start();

// Symfony2 boot
require_once __DIR__.'/../lichess/LichessKernel.php';
use Symfony\Component\HttpFoundation\Request;

// Run application
$kernel = new LichessKernel('prod', false);
$kernel->handle(new Request())->send();

// Display timer
print str_replace('[[time]]', round(1000*(microtime(true) - $start)).'ms', ob_get_clean());
