<?php

function show_run($text, $command, $catch = false)
{
    echo "\n* $text\n$command\n";
    passthru($command, $return);
    if (0 !== $return && !$catch) {
        echo "\n/!\\ The command returned $return\n";
        exit(1);
    }
}

function show_run_catch($text, $command)
{
    show_run($text, $command, true);
}

function read_arg(array $argv, $index, $default = null)
{
    return isset($argv[$index+1]) ? $argv[$index+1] : $default;
}

function show_action($action)
{
    echo "  _ _      _                  \n | (_)    | |                 \n | |_  ___| |__   ___ ___ ___ \n | | |/ __| '_ \ / _ | __/ __|\n | | | (__| | | |  __|__ \__ \ \n |_|_|\___|_| |_|\___|___/___/";

    printf("\n\n %s\n| %s |\n %s", str_repeat('-', strlen($action)+2), $action, str_repeat('-', strlen($action)+2));
}

function clearOpCode()
{
    show_run("Clearing APC opcode cache", "php app/console --env=prod apc:clear --opcode");
}

function maintenance($maintenance = false)
{
    $mode = $maintenance ? "on" : "off";
    show_run("Setting maintenance: $mode", "bin/maintenance $mode");
    clearOpCode();
}

function clearCache($app, $env, $warmup = true)
{
    show_run("Warming up $app cache", "php $app/console --env=$env cache:clear".($warmup ? "" : " --no-warmup"));
    show_run_catch("Creating $app cache", "mkdir -p $app/cache/$env");
    show_run("Raising $app cache permissions", "chmod -R 777 $app/cache/$env");
}

function rebuildBootstrap()
{
    show_run("Building bootstrap", "vendor/bundles/Sensio/Bundle/DistributionBundle/Resources/bin/build_bootstrap.php");
    show_run("Copying  bootstrap", "cp app/bootstrap.* xhr/");
}
