<?php

// Try PreKernelCache
require_once __DIR__.'/../src/Bundle/LichessBundle/PreKernelCache.php';

// Symfony2 boot
require_once __DIR__.'/../lichess/LichessKernel.php';
use Symfony\Component\HttpFoundation\Request;

$kernel = new LichessKernel('prod', false);
$kernel->handle(new Request())->send();
