<?php

require_once __DIR__.'/../miam/MiamKernel.php';

$kernel = new MiamKernel('dev', true);
$kernel->handle()->send();