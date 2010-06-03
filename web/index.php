<?php

require_once __DIR__.'/../miam/MiamKernel.php';

$kernel = new MiamKernel('prod', false);
$kernel->handle()->send();
