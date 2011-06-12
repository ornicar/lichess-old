<?php

// Try PreKernelCache
require_once __DIR__.'/../src/Bundle/LichessBundle/PreKernelCache.php';

// Symfony2 boot
require_once __DIR__.'/../lichess/bootstrap.php.cache';
require_once __DIR__.'/../lichess/LichessKernel.php';

use Symfony\Component\HttpFoundation\Request;

// Run application
$kernel = new LichessKernel('dev', true);
$kernel->loadClassCache();
$kernel->handle(Request::createFromGlobals())->send();
