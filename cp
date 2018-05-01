#!/usr/bin/env php
<?php

if (PHP_SAPI !== 'cli') {
    exit('Script needs to be run from Command Line Interface (cli)');
}

define('COCKPIT_CLI', true);

include_once(__DIR__.'/bootstrap.php');

$_REQUEST = CLI::opts(); // make option available via $app->param()
$app = cockpit();

if (isset($argv[1])) {

    $cmd = str_replace('../', '', $argv[1]);
    $script = $app->path("#config:cli/{$cmd}.php");

    if (!$script) {
        $script = $app->path("#cli:{$cmd}.php");
    }

    switch ($cmd) {

        case 'test':
            CLI::writeln('Yepp!', true);
            break;

        default:

            if ($script) {
                include($script);
            } else {
                CLI::writeln("Error: Command \"{$cmd}\" not found!", false);
            }
    }
}
