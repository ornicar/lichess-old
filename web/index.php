<?php

require_once __DIR__.'/../lichess/LichessKernel.php';

$kernel = new LichessKernel('prod', false);
$kernel->handle()->send();
