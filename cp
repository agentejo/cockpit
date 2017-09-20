#!/usr/bin/env php
<?php

if (PHP_SAPI !== 'cli') {
    exit('Script needs to be run from Command Line Interface (cli)');
}

if (!defined('COCKPIT_CLI')) define('COCKPIT_CLI', true);

include_once(__DIR__."/bootstrap.php");

if (isset($argv[1])) {

    $cmd = str_replace('../', '', $argv[1]);

    switch($cmd) {

        case 'test':
            CLI::writeln("Yepp!", true);
            break;
        default:

            if ($script = cockpit()->path("#config:cli/{$cmd}.php")) {
                include($script);
            } else {
                CLI::writeln("Command - {$cmd} - not found!", false);
            }
    }
}