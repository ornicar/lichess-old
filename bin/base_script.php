<?php

function show_run($text, $command)
{
    echo "\n* $text\n$command\n";
    passthru($command, $return);
    if (0 !== $return) {
        echo "\n/!\\ The command returned $return\n";
        exit(1);
    }
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

function maintenance($maintenance = false)
{
  $mode = $maintenance ? "on" : "off";
  show_run("Setting maintenance: $mode", "bin/maintenance $mode");
}
