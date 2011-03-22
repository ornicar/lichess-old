<?php

// skip toolbar request
$url = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];
if (strpos($url, '_wdt')) {
    return require __DIR__.'/index_dev.php';
}

// start profiling
xhprof_enable();

// Start timer
$start = microtime(true);
ob_start();

// Symfony2 boot
require_once __DIR__.'/../lichess/bootstrap.php';
require_once __DIR__.'/../lichess/LichessKernel.php';

use Symfony\Component\HttpFoundation\Request;

// Run application
$kernel = new LichessKernel('dev', true);
$kernel->handle(Request::createFromGlobals())->send();

// Display timer
print str_replace('[[time]]', round(1000*(microtime(true) - $start)).'ms', ob_get_clean());

// stop profiler
$xhprof_data = xhprof_disable();

$XHPROF_ROOT = '/usr/share/webapps/xhprof';
$source = 'lichess';
$xhProfDomain = 'xhprof';

include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

$xhprof_runs = new XHProfRuns_Default();
$runId = $xhprof_runs->save_run($xhprof_data, $source);

printf('<a href="http://%s/index.php?run=%s&source=%s">Profiler</a>%s', $xhProfDomain, $runId, $source, str_repeat('<br />', 8));
