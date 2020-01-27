#!/usr/bin/env php
<?php

if (PHP_SAPI !== 'cli') {
    exit('Script needs to be run from Command Line Interface (cli)');
}

// Autoload vendor libs
include(__DIR__ . '/lib/vendor/autoload.php');

$configuration = require('app/config.php');
$appPath = __DIR__;
$publicPath = __DIR__;
$app = new \Cockpit\App($appPath, $publicPath, $configuration, \Cockpit\App::MODE_CLI);
$app->boot();

$_REQUEST = CLI::opts(); // make option available via $app->param()

if (isset($argv[1])) {
    $app = $app->cockpit();

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
