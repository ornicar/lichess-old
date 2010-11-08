<?php

require_once __DIR__.'/../src/Bundle/LichessBundle/PreKernelCache.php';
require_once __DIR__.'/../lichess/LichessKernel.php';
use Symfony\Component\HttpFoundation\Request;

$kernel = new LichessKernel('dev', true);
$kernel->handle(new Request())->send();
