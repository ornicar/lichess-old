<?php

// Redirect /index.php to /
if(0 === strncmp($_SERVER['REQUEST_URI'], '/index.php', 10)) {
    $url = substr($_SERVER['REQUEST_URI'], 10);
    header('Location: '.$url, true, 301);
    printf('<html><head><meta http-equiv="refresh" content="1;url=%s"/></head></html>', htmlspecialchars($url, ENT_QUOTES));
    die;
}

// Try PreKernelCache
require_once __DIR__.'/../src/Bundle/LichessBundle/PreKernelCache.php';

// Symfony2 boot
require_once __DIR__.'/../lichess/LichessKernel.php';
use Symfony\Component\HttpFoundation\Request;

$kernel = new LichessKernel('prod', false);
$kernel->handle(new Request())->send();
