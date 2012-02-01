<?php

//include "maintenance.php";

// Try to avoid running the application
require_once __DIR__.'/../src/Bundle/LichessBundle/Boost/Router.php';

// Symfony2 boot
require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';

use Symfony\Component\HttpFoundation\Request;

// Run application
$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$kernel->handle(Request::createFromGlobals())->send();
