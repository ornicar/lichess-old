<?php

//include "maintenance.php";

// Symfony2 boot
require_once __DIR__.'/../xhr/bootstrap.php.cache';
require_once __DIR__.'/../xhr/XhrKernel.php';

use Symfony\Component\HttpFoundation\Request;

// Run application
$kernel = new XhrKernel('prod', false);
$kernel->loadClassCache();
try {
    $kernel->handle(Request::createFromGlobals())->send();
} catch (\Exception $e) {
    // All exceptions are catched but the ResourceNotFound one
    // Make it a 404
    header(sprintf('HTTP/1.0 404 Not Found'));
    echo "Not Found.";
    exit();
}
