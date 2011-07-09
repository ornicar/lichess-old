<?php

function show_run($text, $command)
{
    echo "\n\n* $text\n$command";
    exec($command, $output, $return);
    if (0 !== $return) {
        echo implode("\n", $output);
        exit(1);
    }
}

function show_action($action)
{
    echo "  _ _      _                  \n | (_)    | |                 \n | |_  ___| |__   ___ ___ ___ \n | | |/ __| '_ \ / _ | __/ __|\n | | | (__| | | |  __|__ \__ \ \n |_|_|\___|_| |_|\___|___/___/";

    printf("\n\n %s\n| %s |\n %s", str_repeat('-', strlen($action)+2), $action, str_repeat('-', strlen($action)+2));
}
