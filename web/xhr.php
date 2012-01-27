<?php

//include "maintenance.php";

// Symfony2 boot
require_once __DIR__.'/../xhr/bootstrap.php.cache';
require_once __DIR__.'/../xhr/XhrKernel.php';

use Symfony\Component\HttpFoundation\Request;

// Run application
$kernel = new XhrKernel('prod', false);
$kernel->loadClassCache();
$kernel->handle(Request::createFromGlobals())->send();
