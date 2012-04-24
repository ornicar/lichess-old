<?php

//include "maintenance.php";

// Redirect /index.php to /
if(0 === strncmp($_SERVER['REQUEST_URI'], '/index.php', 10)) {
    $url = substr($_SERVER['REQUEST_URI'], 10);
    header('Location: '.$url, true, 301);
    printf('<html><head><meta http-equiv="refresh" content="1;url=%s"/></head></html>', htmlspecialchars($url, ENT_QUOTES));
    die;
}

// Symfony2 boot
require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';

use Symfony\Component\HttpFoundation\Request;

// Run application
$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
$kernel->handle(Request::createFromGlobals())->send();
